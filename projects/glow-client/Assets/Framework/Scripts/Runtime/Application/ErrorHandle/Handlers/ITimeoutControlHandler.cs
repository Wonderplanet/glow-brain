using System;

namespace WPFramework.Application.ErrorHandle
{
    public interface ITimeoutControlHandler
    {
        bool Handle(Action retry, Action abort);
    }
}
