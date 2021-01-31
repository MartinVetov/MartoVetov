#include <iostream>

#include "types.h"

int main()
{
	type<int, 5> x;
	type<int, 6> y;

	std::cout << x.toString() + y.toString() << std::endl;

	return 0;
}