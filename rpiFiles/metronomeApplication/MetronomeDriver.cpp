#include "MetronomeDriver.h"

MetronomeDriver* MetronomeDriver::instance = NULL;

MetronomeDriver::MetronomeDriver(int arg, int argTwo)
{
    //init mutexes
    if (pthread_mutex_init(&playingMutex, NULL) != 0) {
        printf("\n playing mutex init has failed\n");

        exit(-1);
    }

    if (pthread_mutex_init(&bpmMutex, NULL) != 0) {
        printf("\n bpm mutex init has failed\n");

        exit(-1);
    }

    if (pthread_mutex_init(&outputMutex, NULL) != 0) {
        printf("\n output mutex init has failed\n");

        exit(-1);
    }

    //set up signal handler for button
    signal(SIGTX, static_playPauseHandler);

    //open device file
    fd = open("//dev//metronomeiodev", O_RDWR);
    if (fd < 0) {
        printf("Can't open device file: %s\n", DEVICE_NAME);

        exit(-1);
    }

    //get output gpio number and bpm from arguments
    outputGPIO = arg;
    metronome.setBpm(argTwo);
    metronome.calcSilence();

    //Register this application with the REGISTER_UAPP signal
    if (ioctl(fd, REGISTER_UAPP, NULL))
    {
        perror("Error registering app\n");
        close(fd);

        exit(-1);
    }

    //read initial potentiometer value
    potentiometerValue = readPotentiometer();

    std::cout << "Init potentiometer value" << potentiometerValue << std::endl;

    instance = this;
}

MetronomeDriver::~MetronomeDriver()
{
    printf("\n closing driver file");
    close(fd);

    //if metronome is still playing, pause it
    if (metronome.getPlaying())
    {
        static_playPauseHandler(SIGTX);
    }
}

//thread to run metronome in
void* MetronomeDriver::doMetronome(void)
{
    int ret = 0;

    output.pin = outputGPIO;
    output.value = 1;

    while (metronome.getPlaying())
    {
        ret = ioctl(fd, IOCTL_PIIO_GPIO_WRITE, &output);
        output.value = !output.value;
        usleep(metronome.getSilence());
    }

    output.value = 0;
    ret = ioctl(fd, IOCTL_PIIO_GPIO_WRITE, &output);

    return NULL;
}

void* MetronomeDriver::static_doMetronome(void *arg)
{
    return instance->doMetronome();
}

//thread to be reading potentiometer value in
void* MetronomeDriver::doReadPotentiometer(void)
{
    int tempPotValue = 0;

    while (metronome.getPlaying())
    {
        tempPotValue = readPotentiometer();

        //compensate for potentiometer value read accuracy
        if (abs(potentiometerValue - tempPotValue) > 30)
        {
            potentiometerValue = tempPotValue;

            //translate potentiometer value to valid metronome value
            //SOURCE: Stack Overflow, 2021
            //ACCESSED FROM: https://stackoverflow.com/questions/929103/convert-a-number-range-to-another-range-maintaining-ratio
            int potentiometerRangeDif = potentiometerMax - potentiometerMin;
            int metronomeRangeDif = metronome.getMax() - metronome.getMin();
            int newBpm = (((potentiometerValue - potentiometerMin) * metronomeRangeDif) / potentiometerRangeDif) + metronome.getMin();

            setNewBPM(newBpm);
        }
    }

    return NULL;
}

int MetronomeDriver::readPotentiometer()
{
    unsigned char data[3];
    int potValue = 0;

    //SOURCE: Al-Hertani, 2013
    //ACCESSED FROM: https://web.archive.org/web/20160325211159/http:/hertaville.com/2013/07/24/interfacing-an-spi-adc-mcp3008-chip-to-the-raspberry-pi-using-c/
    data[0] = 1;  //  first byte transmitted -> start bit
    data[1] = 0b10000000 | (((potentiometerChannel & 7) << 4)); // second byte transmitted -> (SGL/DIF = 1, D2=D1=D0=0)
    data[2] = 0; // third byte transmitted....don't care

    //Use mcp3008 to read analogue value
    potentiometer.spiWriteRead(data, sizeof(data));

    potValue = 0;
    potValue = (data[1] << 8) & 0b1100000000; //merge data[1] & data[2] to get result
    potValue |= (data[2] & 0xff);

    return potValue;
}

void* MetronomeDriver::static_doReadPotentiometer(void* arg)
{
    return instance->doReadPotentiometer();
}

//play/pause metronome
void MetronomeDriver::playPause(int sig)
{
    pthread_mutex_lock(&playingMutex);

    //if metronome is playing, stop it
    if (metronome.getPlaying())
    {
        metronome.setPlaying(false);

        pthread_mutex_lock(&outputMutex);
        std::cout << "paused" << std::endl;
        pthread_mutex_unlock(&outputMutex);

        pthread_join(metronomeThread, NULL);
        pthread_join(potentiometerReadThread, NULL);
    }
    //if metronome is not playing, start it
    else
    {
        metronome.setPlaying(true);

        pthread_mutex_lock(&outputMutex);
        std::cout << "playing" << std::endl;
        pthread_mutex_unlock(&outputMutex);

        pthread_create(&metronomeThread, NULL, static_doMetronome, NULL);
        pthread_create(&potentiometerReadThread, NULL, static_doReadPotentiometer, NULL);
    }

    pthread_mutex_unlock(&playingMutex);
}

void MetronomeDriver::static_playPauseHandler(int sig)
{
    instance->playPause(sig);
}

//change bpm of metronome
void MetronomeDriver::adjustBPM(int value)
{
    pthread_mutex_lock(&bpmMutex);

    metronome.setBpm(metronome.getBpm() + value);
    metronome.calcSilence();

    pthread_mutex_lock(&outputMutex);
    std::cout << metronome.getBpm() << std::endl;
    pthread_mutex_unlock(&outputMutex);

    pthread_mutex_unlock(&bpmMutex);
}

void MetronomeDriver::setNewBPM(int value)
{
    pthread_mutex_lock(&bpmMutex);

    metronome.setBpm(value);
    metronome.calcSilence();

    pthread_mutex_lock(&outputMutex);
    std::cout << metronome.getBpm() << std::endl;
    pthread_mutex_unlock(&outputMutex);

    pthread_mutex_unlock(&bpmMutex);
}