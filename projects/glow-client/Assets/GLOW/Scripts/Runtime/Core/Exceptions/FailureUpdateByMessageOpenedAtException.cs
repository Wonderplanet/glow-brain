using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class FailureUpdateByMessageOpenedAtException : WrappedServerErrorException
    {
        public FailureUpdateByMessageOpenedAtException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
