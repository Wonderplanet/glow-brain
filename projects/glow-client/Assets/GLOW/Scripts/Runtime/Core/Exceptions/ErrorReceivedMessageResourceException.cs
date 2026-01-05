using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ErrorReceivedMessageResourceException : WrappedServerErrorException
    {
        public ErrorReceivedMessageResourceException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
