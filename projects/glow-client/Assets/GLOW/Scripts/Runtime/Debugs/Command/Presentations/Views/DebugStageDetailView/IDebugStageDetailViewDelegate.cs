using GLOW.Debugs.Command.Domains.UseCase;

namespace GLOW.Debugs.Command.Presentations.Views.DebugStageDetailView
{
    public interface IDebugStageDetailViewDelegate
    {
        void SetUp(DebugStageSummaryUseCaseModel model);
    }
}