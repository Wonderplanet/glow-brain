using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.AdventBattleRanking.Presentation.Components;
using GLOW.Scenes.AdventBattleRanking.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.AdventBattleRanking.Presentation.Views
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-4_降臨バトルランキング
    /// 　　44-4-2_ランキング画面
    /// </summary>
    public class AdventBattleRankingView : UIView
    {
        [Header("ランキングのタイトル")]
        [SerializeField] UIText _titleText;

        [Header("自分のユーザー")]
        [SerializeField] AdventBattleRankingMyselfUserComponent _myselfUserComponent;
        [Header("自分以外のユーザー")]
        [SerializeField] AdventBattleRankingOtherUserListComponent _otherUserListComponent;

        public void SetUpTitle(AdventBattleName adventBattleName)
        {
            _titleText.SetText(adventBattleName.Value);
        }

        public void SetUpOtherUserComponents(IReadOnlyList<AdventBattleRankingOtherUserViewModel> otherUserViewModels)
        {
            _otherUserListComponent.SetupAndReload(otherUserViewModels);
        }

        public void SetUpMyselfUserComponent(AdventBattleRankingMyselfUserViewModel myselfUserViewModel)
        {
            _myselfUserComponent.SetUpUnitImage(myselfUserViewModel.UnitIconAssetPath);
            _myselfUserComponent.SetUpEmblem(myselfUserViewModel.EmblemIconAssetPath);
            _myselfUserComponent.SetUpRank(myselfUserViewModel.Rank, myselfUserViewModel.CalculatingRankings);
            _myselfUserComponent.SetUpRankingViewStatus(myselfUserViewModel.ViewStatus);
            _myselfUserComponent.SetUpUserName(myselfUserViewModel.UserName);
            _myselfUserComponent.SetUpMaxScore(myselfUserViewModel.MaxScore);
            _myselfUserComponent.SetUpRankIcon(myselfUserViewModel.RankType, myselfUserViewModel.RankLevel);
        }
    }
}
