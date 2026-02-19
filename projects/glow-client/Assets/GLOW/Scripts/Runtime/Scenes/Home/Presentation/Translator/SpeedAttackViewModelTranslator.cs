using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Presentation.ViewModels;

namespace GLOW.Scenes.Home.Presentation.Translator
{
    public static class SpeedAttackViewModelTranslator
    {
        public static SpeedAttackViewModel Translate(SpeedAttackUseCaseModel entity)
        {
            return entity.IsEmpty
                ? SpeedAttackViewModel.Empty
                : new SpeedAttackViewModel(entity.ClearTimeMs, entity.NextGoalTime);
        }
    }
}