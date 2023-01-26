#include <iostream>
#include <string>

#include "MetronomeDriver.h"

int main(int argc, char* argv[])
{
    //create MetronomeDriver
    MetronomeDriver metronomeDriver(strtol(argv[1], NULL, 10), strtol(argv[2], NULL, 10));

    //possible input values
    const std::string exitChar = "X";
    const std::string playChar = "P";
    const std::string upTen = "U10";
    const std::string downTen = "D10";
    const std::string up = "U";
    const std::string down = "D";

    const int downValue = -1;
    const int upValue = 1;
    const int upTenValue = 10;
    const int downTenValue = -10;

    std::string currentInput;

    //loop until the user enters X
    do
    {
        std::cin >> currentInput;

        if (currentInput == playChar)
        {
            //play/pause metronome
            metronomeDriver.static_playPauseHandler(SIGTX);
        }
        else if (currentInput == upTen)
        {
            //speed up metronome
            metronomeDriver.adjustBPM(upTenValue);
        }
        else if (currentInput == downTen)
        {
            //slow down metronome
            metronomeDriver.adjustBPM(downTenValue);
        }
        else if (currentInput == up)
        {
            //speed up metronome
            metronomeDriver.adjustBPM(upValue);
        }
        else if (currentInput == down)
        {
            //slow down metronome
            metronomeDriver.adjustBPM(downValue);
        }

        sleep(1);

    } while (currentInput != exitChar);



    return 0;

}