using GLOW.Core.Domain.Models;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;

namespace GLOW.Scenes.UserLevelUp.Presentation.Translator
{
    public class UserExpGainViewModelTranslator
    {
        public static UserExpGainViewModel ToUserExpGainViewModel(UserExpGainModel userExpGainModel)
        {
            return new UserExpGainViewModel(
                userExpGainModel.Level,
                userExpGainModel.StartExp,
                userExpGainModel.EndExp,
                userExpGainModel.NextLevelExp,
                (userExpGainModel.EndExp == userExpGainModel.NextLevelExp));
        }
    }
}