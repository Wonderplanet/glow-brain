using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class InvalidParameterException : WrappedServerErrorException
    {
        public InvalidParameterException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
