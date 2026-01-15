using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class InGamePreferenceInitializer : IInGamePreferenceInitializer
    {
        [Inject] IInGamePreferenceRepository InGamePreferenceRepository { get; }

        public InGamePreferenceInitializationResult Initialize()
        {
            return new InGamePreferenceInitializationResult(
                InGamePreferenceRepository.InGameBattleSpeed,
                InGamePreferenceRepository.IsInGameAutoEnabled,
                InGamePreferenceRepository.IsInGameContinueSelecting);
        }
    }
}