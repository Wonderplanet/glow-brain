using System;
using WPFramework.Exceptions;

namespace WPFramework.Application.ErrorHandle
{
    public interface IDiskFullExceptionHandler
    {
        bool Handle(DiskFullException exception, Action completion);
    }
}
