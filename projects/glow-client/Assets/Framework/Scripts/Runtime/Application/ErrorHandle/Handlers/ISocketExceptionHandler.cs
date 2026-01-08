using System;
using System.Net.Sockets;

namespace WPFramework.Application.ErrorHandle
{
    public interface ISocketExceptionHandler
    {
        bool Handle(SocketException exception, Action completion);
    }
}
