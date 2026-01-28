using System.Linq;
using GLOW.Scenes.UserLevelUp.Domain.Model;
using GLOW.Scenes.UserLevelUp.Presentation.ViewModel;

namespace GLOW.Scenes.UserLevelUp.Presentation.Translator
{
    public class UserLevelUpInfoViewModelTranslator
    {
        public static UserLevelUpInfoViewModel ToUserLevelUpInfoViewModel(UserLevelUpInfoModel model)
        {
            if (model.IsEmpty())
            {
                return UserLevelUpInfoViewModel.Empty;
            }

            var userLevelUpViewModel =
                UserLevelUpViewModelTranslator.ToUserLevelUpResultViewModel(model.UserLevelUpEffectModel);

            var userExpGainModels = model.UserExpGainModels.Select(
                    UserExpGainViewModelTranslator.ToUserExpGainViewModel)
                .ToList();

            return new UserLevelUpInfoViewModel(
                userLevelUpViewModel,
                userExpGainModels,
                model.CurrentExp,
                model.NextLevelExp);
        }
    }
}
