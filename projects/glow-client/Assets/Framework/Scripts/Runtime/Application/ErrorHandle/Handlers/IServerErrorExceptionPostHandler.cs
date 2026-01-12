using System;
using UnityHTTPLibrary;

namespace WPFramework.Application.ErrorHandle
{
    public interface IServerErrorExceptionPostHandler
    {
        bool Handle(ServerErrorException exception, Action completion);
    }
}
