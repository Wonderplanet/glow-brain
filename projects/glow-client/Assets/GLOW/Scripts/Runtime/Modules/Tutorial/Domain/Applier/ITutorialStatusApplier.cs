using GLOW.Core.Domain.Models.Tutorial;

namespace GLOW.Modules.Tutorial.Domain.Applier
{
    public interface ITutorialStatusApplier
    {
        void UpdateTutorialStatus(TutorialStatusModel tutorialStatusModel);
    }
}
