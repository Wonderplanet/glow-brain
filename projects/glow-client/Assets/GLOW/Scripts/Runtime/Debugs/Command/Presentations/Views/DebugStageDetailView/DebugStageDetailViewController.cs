using System.Linq;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Debugs.Command.Domains.UseCase;
using GLOW.Scenes.DebugStageDetail.Domain;
using UIKit;
using Zenject;

namespace GLOW.Debugs.Command.Presentations.Views.DebugStageDetailView
{
    public class DebugStageDetailViewController : UIViewController<DebugStageDetailView>
    {
        [Inject] IDebugStageDetailViewDelegate ViewDelegate { get; }

        public void SetUp(DebugStageSummaryUseCaseModel useCaseModel)
        {
            ViewDelegate.SetUp(useCaseModel);
        }

        public void UpdateView(DebugStageDetailUseCaseModel useCaseModel)
        {
            ActualView.SetTopText(
                useCaseModel.Summary.QuestName.Value,
                useCaseModel.StageInfos.First().BaseInfo.Difficulty.ToString());

            ActualView.SetSummaryText(useCaseModel.Summary);
            ActualView.SetStageInfoText(useCaseModel.StageInfos, OnSelect);
        }

        void OnSelect(StageNumber stageNumber)
        {
            ActualView.ShowStageInfo(stageNumber);
        }

        [UIAction]
        public void OnClose()
        {
            Dismiss();
        }
        [UIAction]
        public void OnSummaryButtonClick()
        {
            ActualView.ShowSummary();
        }

    }
}
