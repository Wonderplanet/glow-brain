using GLOW.Core.Domain.Models;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.Translators;
using GLOW.Scenes.UserLevelUp.Presentation.ViewModel;

namespace GLOW.Scenes.UserLevelUp.Presentation.Translator
{
    public class UserLevelUpViewModelTranslator
    {
        public static UserLevelUpResultViewModel ToUserLevelUpResultViewModel(UserLevelUpEffectModel model)
        {
            if (model.IsEmpty()) return UserLevelUpResultViewModel.Empty;
            
            var rewards = PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModels(model.PlayerResourceResultModels);
            var groupingRewards = PlayerResourceMerger.Merge(rewards);
            return new UserLevelUpResultViewModel(
                model.UserLevel, 
                groupingRewards, 
                model.BeforeMaxStamina,
                model.AfterMaxStamina,
                model.IsLevelMax);
        }
    }
}