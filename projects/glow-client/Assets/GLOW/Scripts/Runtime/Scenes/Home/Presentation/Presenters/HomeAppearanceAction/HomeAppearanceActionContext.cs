using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Presentation.ValueObjects;
using GLOW.Scenes.Home.Presentation.ViewModels;

namespace GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction
{
    /// <summary>
    /// ホーム画面遷移時のダイアログ・演出処理で共有するコンテキスト
    /// </summary>
    public record HomeAppearanceActionContext(
        HomeMainQuestViewModel HomeMainViewModel,
        DisplayAtLoginUseCaseModel DisplayAtLoginModel)
    {
        public static HomeAppearanceActionContext Empty { get; } = new (
            HomeMainQuestViewModel.Empty,
            DisplayAtLoginUseCaseModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}