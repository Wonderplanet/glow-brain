#if GLOW_INGAME_DEBUG
using GLOW.Debugs.InGame.Domain.Models;

namespace GLOW.Debugs.InGame.Domain.Definitions
{
    public interface IInGameDebugSettingRepository
    {
        void Save(InGameDebugSettingModel selectedStageModel);
        InGameDebugSettingModel Get();
    }
}
#endif
