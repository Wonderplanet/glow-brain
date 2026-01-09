using System;
using UnityHTTPLibrary;

namespace WPFramework.Exceptions.Mappers
{
    public interface IServerErrorExceptionMapper
    {
        Exception Map(ServerErrorException exception);
    }
}
