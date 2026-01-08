using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class AdventBattleSessionMismatchException : DataInconsistencyServerErrorException
    {
        public AdventBattleSessionMismatchException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
