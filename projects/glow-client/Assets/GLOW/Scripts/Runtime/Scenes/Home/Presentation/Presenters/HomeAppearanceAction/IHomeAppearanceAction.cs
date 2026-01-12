using System;
using System.Threading;
using Cysharp.Threading.Tasks;

namespace GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction
{
    /// <summary>
    /// ホーム画面表示時のダイアログ表示・演出処理を抽象化するインターフェース
    /// </summary>
    public interface IHomeAppearanceAction
    {
        /// <summary>
        /// ダイアログ表示や演出を実行する
        /// </summary>
        /// <param name="cancellationToken">キャンセレーショントークン</param>
        /// <param name="context">実行コンテキスト</param>
        /// <returns>処理完了のUniTask</returns>
        UniTask ExecuteAsync(HomeAppearanceActionContext context, Action onCloseCompletion,
            CancellationToken cancellationToken);
    }
}
