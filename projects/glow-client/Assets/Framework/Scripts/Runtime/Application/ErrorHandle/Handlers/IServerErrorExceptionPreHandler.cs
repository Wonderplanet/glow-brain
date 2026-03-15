using System;
using UnityHTTPLibrary;

namespace WPFramework.Application.ErrorHandle
{
    public interface IServerErrorExceptionPreHandler
    {
        bool Handle(ServerErrorException exception, Action completion);
    }
}
