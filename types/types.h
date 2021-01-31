#pragma once

#include <type_traits>
#include <string>

constexpr auto LESS_THAN{ -1 };
constexpr auto EQUAL_TO{ 0 };
constexpr auto GREATER_THAN{ 1 };

/// <summary>
/// Giving default value to all types.
/// </summary>
constexpr auto kDefaultValue{ 0.0 };

/// <summary>
/// A concept that will accept only arithmetic values in Ty parameter.
/// NOTE: std::string gives more functionality no need for unnecessary complications.
/// </summary>
template<typename Ty>
concept Arithmetic = std::is_arithmetic_v<Ty>;

/// <summary>
/// Class that gives safety to all numeric types and their values in C++. Prevents casting and
/// transition from one type to another.
/// </summary>
template<Arithmetic Ty, Ty Value = kDefaultValue>
class type
{
public:
	/// <summary>
	/// </summary>
	/// <returns> Inserted value in the template. </returns>
	Ty value() const noexcept;
	/// <summary>
	/// </summary>
	/// <returns> The type of the instance. </returns>
	std::string getType() const;
	/// <summary>
	/// Compares this instance to a specified type adn value of "other" and returns an integer that
	/// is indicator whether the instance is EQUAL_TO, LESS_THAN, GREATER_THAN than the value specified.
	/// </summary>
	/// <param name="other">The specified value to compare</param>
	/// <returns>
	/// A signed integer indicating the result of the comparision. 1 for greater value, 0 for
	/// equivalent values and -1 for value less than the value of "other". If an error occurs
	/// the returned value is the minimum value of type int.
	/// </returns>
	int compareTo(Ty&& other);
	/// <summary>
	/// Converts the value to a string representation.
	/// </summary>
	/// <returns> A string value. </returns>
	std::string toString();
	// TODO: Operators overloading.
protected:
	Ty m_Var{ Value };

	const Ty _kMaxValue = std::numeric_limits<Ty>::max();
	const Ty _kMinValue = std::numeric_limits<Ty>::min();
};

#include "type_impl.h"