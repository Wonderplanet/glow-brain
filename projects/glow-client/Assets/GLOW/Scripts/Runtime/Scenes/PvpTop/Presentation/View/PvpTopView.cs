using System.Collections.Generic;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using GLOW.Scenes.PvpTop.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.PvpTop.Presentation
{
    public class PvpTopView : UIView
    {
        [Header("上部")]
        [SerializeField] RankingRankIcon _pvpRankIcon;
        [Header("上部/ポイント")]
        [SerializeField] UIText _nextRankUpPointText;
        [SerializeField] UIText _totalPointText;
        [Header("上部/開催期限")]
        [SerializeField] UIText _remainingTimeText;
        [Header("上部/左右ボタン")]
        [SerializeField] GameObject _rankingBallon;
        [SerializeField] GameObject _rankingButtonGrayOutObject;
        [Header("累計ポイント報酬表示")]
        [SerializeField] GameObject _pvpTopTotalScoreRewardObject;
        [SerializeField] UIImage _pvpTopTotalScoreRewardIcon;
        [SerializeField] UIText _pvpTopTotalScoreRewardPointText;

        [Header("対戦相手/更新ボタン")]
        [SerializeField] Button _OpponentRefreshCoolTimeButton;
        [SerializeField] UIText _OpponentRefreshCoolTimeText;
        [SerializeField] GameObject _OpponentRefreshCoolTimeGrayOutObject;
        [Header("対戦相手")]
        [SerializeField] PvpTopOpponentComponent _pvpTopOpponentComponent1;
        [SerializeField] PvpTopOpponentComponent _pvpTopOpponentComponent2;
        [SerializeField] PvpTopOpponentComponent _pvpTopOpponentComponent3;
        [SerializeField] ChildScaler _opponentsChildScaler;

        [Header("下部ボタン")]
        [SerializeField] PvpBattleTopStartButtonComponent _pvpBattleTopStartButtonComponent;


        public void InitializeView()
        {
            _OpponentRefreshCoolTimeText.gameObject.SetActive(false);
            _pvpRankIcon.IsVisible = false;
            _pvpTopOpponentComponent1.IsVisible = false;
            _pvpTopOpponentComponent2.IsVisible = false;
            _pvpTopOpponentComponent3.IsVisible = false;
            _pvpBattleTopStartButtonComponent.Initialize();
            _remainingTimeText.SetText("");
        }

        public void SetOpponentRefreshCoolTime(PvpOpponentRefreshCoolTime coolTime)
        {
            SetOpponentRefreshButton(
                !coolTime.HasCoolTime(),
                coolTime.HasCoolTime(),
                coolTime.ToViewString());
        }

        public void SetOpponentRefreshButtonGrayOut()
        {
            SetOpponentRefreshButton(
                false,
                false,
                "");
        }

        public void UpdatePartyName(PartyName partyName)
        {
            _pvpBattleTopStartButtonComponent.SetUpPartyName(partyName);
        }

        public void SetUpView(PvpTopViewModel viewModel)
        {
            // ランクアイコン
            _pvpRankIcon.IsVisible = true;
            _pvpRankIcon.SetupRankType(viewModel.PvpTopUserState.PvpUserRankStatus.ToRankType());
            _pvpRankIcon.PlayRankTierAnimation(viewModel.PvpTopUserState.PvpUserRankStatus.ToScoreRankLevel());

            //ユーザー情報
            _nextRankUpPointText.SetText(viewModel.PvpTopUserState.NextRankUpPoint.ToDisplayString());
            _totalPointText.SetText(viewModel.PvpTopUserState.TotalPoint.ToDisplayString());
            SetUpTotalScoreReward(viewModel.PvpTopNextTotalScoreRewardViewModel);

            // pvp情報
            _rankingBallon.SetActive(viewModel.PvpTopRankingState.PvpRankingOpeningType == PvpRankingOpeningType.Calculating);
            _rankingButtonGrayOutObject.SetActive(viewModel.PvpTopRankingState.IsRankingButtonGrayOutVisible());
            _remainingTimeText.SetText(TimeSpanFormatter.FormatUntilEnd(viewModel.RemainingTimeSpan));

            //対戦相手
            SetUpOpponentComponents(viewModel.OpponentViewModels);

            //編成・バトル開始
            _pvpBattleTopStartButtonComponent.SetUpEditAndStartButton(
                viewModel.PartyName,
                viewModel.PvpTopUserState.PvpChallengeStatus);
            
            // 編成ボタンの特別ルール吹き出し
            _pvpBattleTopStartButtonComponent.SetSpecialRuleIconVisible(viewModel.HasInGameSpecialRuleUnitStatus);
        }

        public void SelectOpponent(PvpOpponentNumber number, PvpChallengeStatus status)
        {
            var index = number.ToIndex();
            _pvpTopOpponentComponent1.Select(index == 0);
            _pvpTopOpponentComponent2.Select(index == 1);
            _pvpTopOpponentComponent3.Select(index == 2);

            // 単一原則に反するが選択状態で開始ボタンのグレーアウト状態が変化するので、連動させる形でここに記述する
            SetStartButtonInteractable(number.IsValid() && status.CanBeChallengeable());
        }

        void SetStartButtonInteractable(bool interactable)
        {
            _pvpBattleTopStartButtonComponent.SetStartButtonInteractable(interactable);
        }

        public void SetUpOpponentComponents(IReadOnlyList<PvpTopOpponentViewModel> viewModels)
        {
            // 初期化
            _pvpTopOpponentComponent1.IsVisible = false;
            _pvpTopOpponentComponent2.IsVisible = false;
            _pvpTopOpponentComponent3.IsVisible = false;

            if(viewModels.Count <= 0) return;
            _pvpTopOpponentComponent1.IsVisible = true;
            _pvpTopOpponentComponent1.Setup(viewModels[0]);

            if(viewModels.Count <= 1) return;
            _pvpTopOpponentComponent2.IsVisible = true;
            _pvpTopOpponentComponent2.Setup(viewModels[1]);

            if(viewModels.Count <= 2) return;
            _pvpTopOpponentComponent3.IsVisible = true;
            _pvpTopOpponentComponent3.Setup(viewModels[2]);

            // アニメーション
            _opponentsChildScaler.Play();
        }

        void SetOpponentRefreshButton(
            bool interactable,
            bool timeTextVisible,
            string timeText)
        {
            _OpponentRefreshCoolTimeButton.interactable = interactable;
            _OpponentRefreshCoolTimeText.gameObject.SetActive(timeTextVisible);
            _OpponentRefreshCoolTimeText.SetText(timeText);
            _OpponentRefreshCoolTimeGrayOutObject.SetActive(!interactable);
        }

        void SetUpTotalScoreReward(PvpTopNextTotalScoreRewardViewModel rewardViewModel)
        {
            _pvpTopTotalScoreRewardObject.SetActive(!rewardViewModel.IsEmpty());
            
            if (rewardViewModel.IsEmpty()) return;
            
            UISpriteUtil.LoadSpriteWithFade(
                _pvpTopTotalScoreRewardIcon.Image,
                rewardViewModel.NextTotalScoreReward.AssetPath.Value);
            _pvpTopTotalScoreRewardPointText.SetText(rewardViewModel.NextTotalScore.ToDisplayString());
        }
    }
}
