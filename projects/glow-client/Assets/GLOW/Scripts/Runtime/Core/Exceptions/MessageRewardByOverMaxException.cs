using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class MessageRewardByOverMaxException : WrappedServerErrorException
    {
        public MessageRewardByOverMaxException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}