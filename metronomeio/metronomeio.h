/*
 ============================================================================
 Name        : metronomeio.c
 Author      : Elizabeth Blogg
 Version     : 0.2
 Copyright   : See Abertay copyright notice
 Description : GPIO Driver for button interrupts and writing/reading GPIO pins
 ============================================================================
 */
#ifndef METRONOMEIO_H
#define METRONOMEIO_H

#include <linux/ioctl.h>

typedef struct lkm_data {
	unsigned char data[256];
	unsigned long len;
	char type;
} lkm_data;

typedef struct gpio_pin {
	char desc[16];
	unsigned int pin;
	int value;
	char opt;
} gpio_pin;

#define IOCTL_PIIO_GPIO_READ 0x67
#define IOCTL_PIIO_GPIO_WRITE 0x68

#define REGISTER_UAPP _IO('R', 'g')

#define  DEVICE_NAME "metronomeiodev"
#define  CLASS_NAME  "metronomeiocls"

#endif
