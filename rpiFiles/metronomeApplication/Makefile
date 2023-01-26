all: main.cpp MetronomeDriver.cpp Metronome.cpp mcp3008Spi.cpp
	/home/cmp408/tools/arm-bcm2708/arm-linux-gnueabihf/bin/arm-linux-gnueabihf-g++ -g -Wall -lpthread -o metronomeApplication main.cpp MetronomeDriver.cpp Metronome.cpp mcp3008Spi.cpp

clean: 
	$(RM) metronomeApplication
