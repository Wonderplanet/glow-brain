using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class MissionCannotReceiveRewardException : WrappedServerErrorException
    {
        public MissionCannotReceiveRewardException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
