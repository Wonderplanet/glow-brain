using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class TutorialBackGroundDownloadUseCase
    {
        [Inject] IGameRepository GameRepository { get; }

        public GameVersionModel GetGameVersion()
        {
            return GameRepository.GetGameVersion();
        }
    }
}