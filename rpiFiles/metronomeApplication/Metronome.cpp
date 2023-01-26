#include "Metronome.h"

Metronome::Metronome()
{
	bpm = 80;
	silence = 100000;
	playing = false;

	calcSilence();
}

//getters
int Metronome::getBpm()
{
	return bpm;
}

int Metronome::getSilence()
{
	return silence;
}

bool Metronome::getPlaying()
{
	return playing;
}

int Metronome::getMax()
{
	return maxBpm;
}

int Metronome::getMin()
{
	return minBpm;
}

//setters
void Metronome::setBpm(int value)
{
	//check bpm is within range
	if (value > maxBpm || value <= minBpm)
	{
		return;
	}

	bpm = value;
}

void Metronome::setPlaying(bool value)
{
	playing = value;
}

//calc the silence between metronome pulses
void Metronome::calcSilence()
{
	silence = 60000000 / bpm / 2;
}