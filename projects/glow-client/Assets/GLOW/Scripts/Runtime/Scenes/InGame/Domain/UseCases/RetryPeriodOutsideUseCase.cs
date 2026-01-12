using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class RetryPeriodOutsideUseCase
    {
        [Inject] IResumableStateRepository ResumableStateRepository { get; }
        [Inject] IInGamePreferenceRepository InGamePreferenceRepository { get; }
        
        public void ClearResumableState()
        {
            InGamePreferenceRepository.IsInGameContinueSelecting = InGameContinueSelectingFlag.False;
            ResumableStateRepository.Clear();
        }
    }
}