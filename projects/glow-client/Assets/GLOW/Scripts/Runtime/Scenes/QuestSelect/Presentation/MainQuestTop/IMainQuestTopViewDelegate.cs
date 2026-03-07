using System.Threading;
using System.Threading.Tasks;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Modules.InAppReview.Domain.ValueObject;

namespace GLOW.Scenes.MainQuestTop.Presentation
{
    public interface IMainQuestTopViewDelegate
    {
        void OnViewWillAppear();

        void OnDeckButtonEdit(MasterDataId selectedStageId);

        void OnDifficultySelectedAndUpdateRepository(MasterDataId mstGroupQuestId, Difficulty difficulty);
        void OnQuestSelected();

        void OnInGameSpecialRuleTapped(MasterDataId mstStageId);
        UniTask ShowQuestReleaseView(
            ShowQuestReleaseAnimation showQuestReleaseAnimation,
            InAppReviewFlag isInAppReviewDisplay,
            CancellationToken cancellationToken);

        void OnClose();
    }
}
