using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class AdventBattleTypeNotFoundException : WrappedServerErrorException
    {
        public AdventBattleTypeNotFoundException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
