using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class QuestTypeNotFoundException : WrappedServerErrorException
    {
        public QuestTypeNotFoundException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
