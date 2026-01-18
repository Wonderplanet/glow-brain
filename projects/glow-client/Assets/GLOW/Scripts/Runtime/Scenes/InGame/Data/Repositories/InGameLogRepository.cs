using GLOW.Scenes.InGame.Domain.Models.LogModel;
using GLOW.Scenes.InGame.Domain.Repositories;

namespace GLOW.Scenes.InGame.Data.Repositories
{
    public class InGameLogRepository : IInGameLogRepository
    {
        InGameLogModel _inGameLogModel = InGameLogModel.Empty;

        public void SetLog(InGameLogModel inGameLogModel)
        {
            _inGameLogModel = inGameLogModel;
        }

        public InGameLogModel GetLog()
        {
            return _inGameLogModel;
        }
    }
}
