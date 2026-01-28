using GLOW.Core.Domain.Repositories;
using GLOW.Modules.Tutorial.Domain.Definitions;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class CheckConfirmTutorialGachaUseCase
    {
        [Inject] IGameRepository GameRepository { get; }

        public bool CheckConfirmTutorialGacha()
        {
            return GameRepository.GetGameFetchOther().TutorialStatus.IsGachaConfirmed();
        }
    }
}