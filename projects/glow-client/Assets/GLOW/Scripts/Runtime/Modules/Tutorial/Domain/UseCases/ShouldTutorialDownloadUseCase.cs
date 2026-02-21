using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class ShouldTutorialDownloadUseCase
    {
        [Inject] IGameRepository GameRepository { get; }

        public bool ShouldTutorialDownload()
        {
            var tutorialStatus = GameRepository.GetGameFetchOther().TutorialStatus;

            // チュートリアル進行度が導入パートの場合通常ダウンロードはスキップしてバックグラウンドダウンロードを行う
            return tutorialStatus.ShouldSetName() || tutorialStatus.IsIntroduction();
        }
    }
}
