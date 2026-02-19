using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class EncyclopediaRewardReceivedException : DataInconsistencyServerErrorException
    {
        public EncyclopediaRewardReceivedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
