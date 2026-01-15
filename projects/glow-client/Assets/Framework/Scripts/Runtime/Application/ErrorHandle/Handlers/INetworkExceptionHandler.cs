using System;
using UnityHTTPLibrary;

namespace WPFramework.Application.ErrorHandle
{
    public interface INetworkExceptionHandler
    {
        bool Handle(NetworkException exception, Action completion);
    }
}
