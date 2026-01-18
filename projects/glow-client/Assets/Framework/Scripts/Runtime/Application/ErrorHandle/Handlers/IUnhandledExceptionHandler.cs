using System;

namespace WPFramework.Application.ErrorHandle
{
    public interface IUnhandledExceptionHandler
    {
        bool Handle(Exception exception, Action completion);
    }
}
