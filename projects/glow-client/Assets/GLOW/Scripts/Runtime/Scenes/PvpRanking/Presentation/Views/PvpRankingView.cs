using System.Collections.Generic;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PvpRanking.Presentation.Components;
using GLOW.Scenes.PvpRanking.Presentation.ViewModels;
using GLOW.Scenes.PvpRanking.Domain.ValueObjects;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Components;

namespace GLOW.Scenes.PvpRanking.Presentation.Views
{
    /// <summary>
    /// 決闘
    /// 　決闘ランキング
    /// 　　決闘ランキング画面
    /// </summary>
    public class PvpRankingView : UIView
    {
        [Header("自分のユーザー")]
        [SerializeField] PvpRankingMyselfUserComponent _myselfUserComponent;
        [Header("自分以外のユーザー")]
        [SerializeField] PvpRankingRankingOtherUserListComponent _otherUserListComponent;

        [Header("ボタン")]
        [SerializeField] UIToggleableComponent _currentRankingComponent;
        [SerializeField] UIToggleableComponent _previousRankingComponent;

        [Header("ランキングの帯")]
        [SerializeField] GameObject _currentMyRankingBandObject;
        [SerializeField] GameObject _prevMyRankingBandObject;
        [SerializeField] GameObject _currentOtherRankingBandObject;
        [SerializeField] GameObject _prevOtherRankingBandObject;

        public void SetUpOtherUserComponents(IReadOnlyList<PvpRankingOtherUserViewModel> otherUserViewModels)
        {
            _otherUserListComponent.SetupAndReload(otherUserViewModels);
        }

        public void SetUpMyselfUserComponent(PvpRankingMyselfUserViewModel myselfUserViewModel)
        {
            _myselfUserComponent.SetUpUnitImage(myselfUserViewModel.UnitIconAssetPath);
            _myselfUserComponent.SetUpEmblem(myselfUserViewModel.EmblemIconAssetPath);
            _myselfUserComponent.SetUpRank(myselfUserViewModel.Rank, myselfUserViewModel.CalculatingRankings);
            _myselfUserComponent.SetUpRankingViewStatus(myselfUserViewModel.ViewStatus);
            _myselfUserComponent.SetUpUserName(myselfUserViewModel.UserName);
            _myselfUserComponent.SetUpScore(myselfUserViewModel.Score);
            _myselfUserComponent.SetUpPvpRankIcon(myselfUserViewModel.PvpUserRankStatus);
        }

        public void UpdateButtons(bool isPrevRanking)
        {
            _currentRankingComponent.IsToggleOn = !isPrevRanking;
            _previousRankingComponent.IsToggleOn = isPrevRanking;
        }

        public void SetUpCurrentRankingBand(bool isPrevRanking)
        {
            _currentMyRankingBandObject.SetActive(!isPrevRanking);
            _prevMyRankingBandObject.SetActive(isPrevRanking);
            _currentOtherRankingBandObject.SetActive(!isPrevRanking);
            _prevOtherRankingBandObject.SetActive(isPrevRanking);
        }
    }
}
