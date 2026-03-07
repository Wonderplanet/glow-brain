using GLOW.Scenes.ComeBackDailyBonus.Domain.Model;
using GLOW.Scenes.ComeBackDailyBonus.Presentation.ViewModel;

namespace GLOW.Scenes.ComeBackDailyBonus.Presentation.Factory
{
    public interface IComebackDailyBonusViewModelFactory
    {
        ComebackDailyBonusViewModel Create(ComebackDailyBonusModel model);
    }
}