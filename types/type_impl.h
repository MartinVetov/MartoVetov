#pragma once
#include "types.h"

template<Arithmetic Ty, Ty Value>
Ty type<Ty, Value>::value() const noexcept
{
	return Value;
}

template<Arithmetic Ty, Ty Value>
std::string type<Ty, Value>::getType() const
{
	return typeid(this->m_Var).name();
}

template<Arithmetic Ty, Ty Value>
inline int type<Ty, Value>::compareTo(Ty&& other)
{
	if (std::move(m_Var) > other) {
		return GREATER_THAN;
	} else if (std::move(m_Var) < other) {
		return LESS_THAN;
	} else if (std::move(m_Var) == other) {
		return EQUAL_TO;
	} else {
		return _kMinValue;
	}
}

template<Arithmetic Ty, Ty Value>
inline std::string type<Ty, Value>::toString()
{
	return std::to_string(m_Var);
}
