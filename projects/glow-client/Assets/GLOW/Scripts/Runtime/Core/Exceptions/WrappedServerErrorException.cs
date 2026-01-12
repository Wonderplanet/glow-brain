using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class WrappedServerErrorException : ServerErrorException
    {
        public WrappedServerErrorException(ServerErrorException serverErrorException)
            : base(
                serverErrorException.Message,
                serverErrorException.ServerErrorCode,
                serverErrorException.ServerErrorMessage,
                serverErrorException.StatusCode,
                serverErrorException.InnerException)
        {
        }
    }
}