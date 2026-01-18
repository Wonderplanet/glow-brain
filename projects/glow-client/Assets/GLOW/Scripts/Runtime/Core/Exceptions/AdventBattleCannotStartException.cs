using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class AdventBattleCannotStartException : DataInconsistencyServerErrorException
    {
        public AdventBattleCannotStartException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
