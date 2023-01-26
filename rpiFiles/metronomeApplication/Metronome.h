#pragma once

class Metronome
{
private:
	int bpm;
	int silence;
	bool playing;

	//range based on valid usleep values
	static const int maxBpm = 250;
	static const int minBpm = 31;

public:
	Metronome();

	//getters
	int getBpm();
	int getSilence();
	bool getPlaying();

	int getMax();
	int getMin();

	//setters
	void setBpm(int);
	void setPlaying(bool);

	void calcSilence();
};

