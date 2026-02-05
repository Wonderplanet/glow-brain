using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class FailureUpdateByUserMessagesException : WrappedServerErrorException
    {
        public FailureUpdateByUserMessagesException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
