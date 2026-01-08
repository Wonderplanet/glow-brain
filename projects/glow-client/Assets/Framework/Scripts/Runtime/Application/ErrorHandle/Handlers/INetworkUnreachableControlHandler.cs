using System;

namespace WPFramework.Application.ErrorHandle
{
    public interface INetworkUnreachableControlHandler
    {
        bool Handle(Action retry, Action abort);
    }
}
