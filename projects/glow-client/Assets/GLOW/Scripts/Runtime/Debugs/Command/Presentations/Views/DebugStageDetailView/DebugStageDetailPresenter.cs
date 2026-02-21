using GLOW.Debugs.Command.Domains.UseCase;
using GLOW.Scenes.DebugStageDetail.Domain;
using Zenject;

namespace GLOW.Debugs.Command.Presentations.Views.DebugStageDetailView
{
    public class DebugStageDetailPresenter : IDebugStageDetailViewDelegate
    {
        [Inject] DebugStageDetailViewController Controller { get; }
        [Inject] DebugStageDetailUseCase DebugStageDetailUseCase { get; }

        void IDebugStageDetailViewDelegate.SetUp(DebugStageSummaryUseCaseModel model)
        {
            var useCaseModel = DebugStageDetailUseCase.GetModel(model);
            Controller.UpdateView(useCaseModel);
        }
    }
}
