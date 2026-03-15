using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class StageCannotReceiveTreasureException : WrappedServerErrorException
    {
        public StageCannotReceiveTreasureException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
