/*
 ============================================================================
 Name        : metronomeio.c
 Author      : Elizabeth Blogg
 Version     : 0.2
 Copyright   : See Abertay copyright notice
 Description : GPIO Driver for button interrupts and writing/reading GPIO pins
 ============================================================================
 */
#include "metronomeio.h"

#include <linux/kernel.h>
#include <linux/module.h>
#include <linux/interrupt.h>
#include <linux/gpio.h>
#include <linux/cdev.h>
#include <linux/fs.h>
#include <linux/sched/signal.h>
#include <linux/ioctl.h>

static int DevBusy = 0;
static int MajorNum = 100;
static struct class*  ClassName  = NULL;
static struct device* DeviceName = NULL;

//pin number that the GPIO is mapped to
unsigned int irq_number;

static unsigned int button = 16;

static unsigned int Irqnum = 0;

//store gpio data
gpio_pin apin;

//store a reference to the userspace application
static struct task_struct *task = NULL;

#define SIGNR 44

static int device_open(struct inode *inode, struct file *file){
	printk(KERN_INFO "metronomeio: device_open(%p)\n", file);

	if (DevBusy)
		return -EBUSY;

	DevBusy++;
	try_module_get(THIS_MODULE);
	return 0;
}

//handle interrupt
static irq_handler_t piirq_irq_handler(unsigned int irq, void *dev_id, struct pt_regs *regs){
    struct siginfo info;
    printk("gpio_irq_signal: Interrupt was triggered and ISR was called\n");

    //send signal to userspace application
    if (task != NULL)
    {
        memset(&info, 0, sizeof(info));
        info.si_signo = SIGNR;
        info.si_code = SI_QUEUE;

        if (send_sig_info(SIGNR, (struct kernel_siginfo *) &info, task) < 0)
        {
            printk("gpio_irq_signal: Error sending signal\n");
        }
    }

   return (irq_handler_t) IRQ_HANDLED;
}

static int my_close(struct inode *inode, struct file *file){
    printk(KERN_INFO "metronomeio: my_close");
    DevBusy--;

	module_put(THIS_MODULE);

    //release the userspace app reference
    if (task != NULL)
    {
        task = NULL;
    }

	return 0;
}

static int device_ioctl(struct file *file, unsigned int cmd, unsigned long arg){
	printk("metronomeio: Device IOCTL invoked : 0x%x - %u\n" , cmd , cmd);

	switch (cmd) {
    case REGISTER_UAPP:
        //get userspace app information
        task = get_current();
        printk("gpio_irq_signal: Userspace app with PID: %d is registered\n", task->pid);
        break;
    case IOCTL_PIIO_GPIO_READ:
        //read GPIO pin
		memset(&apin , 0, sizeof(apin));
		copy_from_user(&apin, (gpio_pin *)arg, sizeof(gpio_pin));
		gpio_request(apin.pin, apin.desc);
		apin.value = gpio_get_value(apin.pin);
		strcpy(apin.desc, "LKMpin");
		copy_to_user((void *)arg, &apin, sizeof(gpio_pin));
		printk("metronomeio: IOCTL_PIIO_GPIO_READ: pi:%u - val:%i - desc:%s\n" , apin.pin , apin.value , apin.desc);
		break;
	case IOCTL_PIIO_GPIO_WRITE:
        //write to GPIO pin
		copy_from_user(&apin, (gpio_pin *)arg, sizeof(gpio_pin));
		gpio_request(apin.pin, apin.desc);
		gpio_direction_output(apin.pin, 0);
		gpio_set_value(apin.pin, apin.value);
		printk("metronomeio: IOCTL_PIIO_GPIO_WRITE: pi:%u - val:%i - desc:%s\n" , apin.pin , apin.value , apin.desc);
		break;
	default:
			printk("metronomeio: command format error\n");
	}

	return 0;
}

struct file_operations Fops = {
    .owner = THIS_MODULE,
	.unlocked_ioctl = device_ioctl,
	.release = my_close,
    .open = device_open,
};

static int __init metronomeio_init(void){
	int result = 0;
    pr_info("%s\n", __func__);
    /* https://www.kernel.org/doc/Documentation/pinctrl.txt */

    printk(KERN_INFO "metronomeio: Initializing driver\n");

    if (!gpio_is_valid(button)){
    	printk(KERN_INFO "metronomeio: invalid GPIO\n");
    return -ENODEV;
   }

    
    //set up character device
    MajorNum = register_chrdev(0, DEVICE_NAME, &Fops);
    if (MajorNum<0){
        printk(KERN_ALERT "metronomeio: failed to register a major number\n");
        return MajorNum;
    }
    printk(KERN_INFO "metronomeio: registered with major number %d\n", MajorNum);

    ClassName = class_create(THIS_MODULE, CLASS_NAME);
    if (IS_ERR(ClassName)){
        unregister_chrdev(MajorNum, DEVICE_NAME);
        printk(KERN_ALERT "metronomeio: Failed to register device class\n");
        return PTR_ERR(ClassName);
    }
    printk(KERN_INFO "metronomeio: device class registered\n");

    DeviceName = device_create(ClassName, NULL, MKDEV(MajorNum, 0), NULL, DEVICE_NAME);
    if (IS_ERR(DeviceName)){
        class_destroy(ClassName);
        unregister_chrdev(MajorNum, DEVICE_NAME);
        printk(KERN_ALERT "metronomeio: Failed to create the device\n");
        return PTR_ERR(DeviceName);
    }

    printk(KERN_INFO "metronomeio: device class created\n");

    //set up button interrupt handling
    gpio_request(button, "Button");
    gpio_direction_input(button);
    gpio_set_debounce(button, 500);
    gpio_export(button, false);

    Irqnum = gpio_to_irq(button);
    printk(KERN_INFO "metronomeio: The button is mapped to IRQ: %d\n", Irqnum);

    result = request_irq(Irqnum,
		  (irq_handler_t) piirq_irq_handler,
		  IRQF_TRIGGER_RISING,
		  DEVICE_NAME,
		  NULL);

    printk("metronomeio loaded\n");
    return 0;
}

static void __exit metronomeio_exit(void){
    free_irq(Irqnum, NULL);
    gpio_unexport(button);
    gpio_free(button);
    device_destroy(ClassName, MKDEV(MajorNum, 0));
    class_unregister(ClassName);
    class_destroy(ClassName);
    unregister_chrdev(MajorNum, DEVICE_NAME);

    gpio_free(apin.pin);

    printk("metronomeio unloaded\n");
}

module_init(metronomeio_init);
module_exit(metronomeio_exit);
MODULE_LICENSE("GPL");
MODULE_AUTHOR("CMP408 - Elizabeth Blogg");
MODULE_DESCRIPTION("RPi GPIO Driver");
MODULE_VERSION("0.2");
