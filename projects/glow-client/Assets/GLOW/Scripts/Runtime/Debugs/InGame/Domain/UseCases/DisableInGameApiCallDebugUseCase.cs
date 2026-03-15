#if GLOW_INGAME_DEBUG
using GLOW.Debugs.InGame.Domain.Definitions;
using Zenject;

namespace GLOW.Debugs.InGame.Domain.UseCases
{
    public class DisableInGameApiCallDebugUseCase
    {
        [Inject] IInGameDebugSettingRepository InGameDebugSettingRepository { get; }

        public void DisableApiCall()
        {
            var model = InGameDebugSettingRepository.Get();

            var newModel = model with { IsSkipApi = true };

            InGameDebugSettingRepository.Save(newModel);
        }
    }
}
#endif