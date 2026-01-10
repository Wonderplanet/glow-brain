using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Presentation.ViewModels;

namespace GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction
{
    /// <summary>
    /// ホーム画面遷移時のダイアログ表示・演出を統合、実行するインターフェース
    /// </summary>
    public interface IHomeAppearanceActionExecutor
    {
        /// <summary>
        /// HomeAppearanceActionのリストをセットする
        /// リストの要素順が実行順になる
        /// </summary>
        void SetActions(IReadOnlyList<IHomeAppearanceAction> actions);

        /// <summary>
        /// ホーム画面遷移時時のダイアログ行事・演出を順次実行する
        /// </summary>
        UniTask ExecuteActions(
            CancellationToken cancellationToken,
            HomeMainQuestViewModel homeMainViewModel,
            DisplayAtLoginUseCaseModel displayAtLoginModel,
            Action onCloseCompletion);
    }
}
