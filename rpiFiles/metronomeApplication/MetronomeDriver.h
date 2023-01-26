#pragma once
#include <cstdio>
#include <cstdlib>
#include <fcntl.h>
#include <unistd.h>
#include <sys/ioctl.h>
#include <csignal>

#include <pthread.h>

#include <ctype.h>

#include <linux/ioctl.h>
#include <iostream>
#include <string>

#include "Metronome.h"
#include "mcp3008Spi.h"

//constants
#define SIGTX 44

#define IOCTL_PIIO_GPIO_READ 0x67
#define IOCTL_PIIO_GPIO_WRITE 0x68

#define REGISTER_UAPP _IO('R', 'g')

#define DEVICE_NAME "metronomeiodev"

class MetronomeDriver
{
public:
	typedef struct gpio_pin {
		char desc[16];
		unsigned int pin;
		int value;
		char opt;
	} gpio_pin;

	static void static_playPauseHandler(int sig);
	void adjustBPM(int);

	MetronomeDriver(int, int);
	~MetronomeDriver();

private:
	//dev file
	int fd;

	//output pin
	gpio_pin output;
	int outputGPIO;
	static const int potentiometerChannel = 0;

	//threading variables
	pthread_t metronomeThread;
	pthread_t potentiometerReadThread;

	pthread_mutex_t playingMutex;
	pthread_mutex_t bpmMutex;

	pthread_mutex_t outputMutex;

	//store metronome information
	Metronome metronome;

	//variable to interface the potentiometer
	mcp3008Spi potentiometer;
	int potentiometerValue;

	//potentiometer range
	static const int potentiometerMax = 1023;
	static const int potentiometerMin = 1;

	void playPause(int);
	void* doMetronome(void);
	void* doReadPotentiometer(void);

	void setNewBPM(int);

	int readPotentiometer();

	//static function wrappers
	static void* static_doMetronome(void*);
	static void* static_doReadPotentiometer(void*);

	static MetronomeDriver* instance;
};

