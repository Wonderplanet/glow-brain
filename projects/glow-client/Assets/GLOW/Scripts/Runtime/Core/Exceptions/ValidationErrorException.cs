using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ValidationErrorException : WrappedServerErrorException
    {
        public ValidationErrorException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
