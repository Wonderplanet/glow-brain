using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle.AutoPlayer;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class SwitchAutoUseCase
    {
        [Inject] IInGamePreferenceRepository InGamePreferenceRepository { get; }
        [Inject(Id = AutoPlayer.PlayerAutoPlayerBindId)] IAutoPlayer PlayerAutoPlayer { get; }

        public InGameAutoEnabledFlag SwitchAuto()
        {
            var isAutoEnabled = !InGamePreferenceRepository.IsInGameAutoEnabled;
            InGamePreferenceRepository.IsInGameAutoEnabled = isAutoEnabled;
            
            PlayerAutoPlayer.IsEnabled = isAutoEnabled.ToAutoPlayerEnabledFlag();
            
            return isAutoEnabled;
        }
    }
}