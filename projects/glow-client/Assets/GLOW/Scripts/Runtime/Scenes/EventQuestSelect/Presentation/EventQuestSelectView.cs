using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.EventQuestSelect.Domain.ValueObject;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.EventQuestSelect.Presentation
{
    /// <summary>
    /// 42_イベントステージ
    /// 　42-1_イベントクエスト
    /// 　　42-1-2_いいジャン祭トップ画面（クエスト選択画面）
    /// </summary>
    public class EventQuestSelectView : UIView
    {
        [Header("コンテンツ")]
        [SerializeField] RectTransform _backgroundArea;
        [SerializeField] UIText _remainingAtText;
        [Header("コンテンツ/クエスト一覧")]
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] GameObject _noContentText;
        [SerializeField] ChildScaler _childScaler;
        [Header("キャンペーン")]
        [SerializeField] GameObject _campaignBalloonGameObject;
        [SerializeField] EventCampaignBalloon _eventCampaignBalloon;
        [Header("イベント交換所")]
        [SerializeField] GameObject _eventExchangeShopObj;
        [Header("ミッション")]
        [SerializeField] GameObject _missionBadgeObj;
        [Header("降臨バトル")]
        [SerializeField] Button _adventBattleButton;
        [SerializeField] GameObject _adventBattleGrayOutObj;
        [SerializeField] UIText _adventBattleName;
        [Header("降臨バトル/時間")]
        [SerializeField] GameObject _adventBattleRemainingTimeObj;
        [SerializeField] UIText _adventBattleRemainingTimePrefixText;
        [SerializeField] UIText _adventBattleRemainingTimeText;
        [Header("Cellタイトルバー")]
        [SerializeField] UIText _cellTitleEventOpenText;
        [SerializeField] UIText _cellTitleEventClosedText;

        public void Initialize()
        {
            _noContentText.SetActive(false);
            _adventBattleButton.gameObject.SetActive(false);
            _cellTitleEventOpenText.gameObject.SetActive(false);
            _cellTitleEventClosedText.gameObject.SetActive(false);
        }

        public void InitCollectionView(IUICollectionViewDelegate delegateObj, IUICollectionViewDataSource dataSource)
        {
            _collectionView.Delegate = delegateObj;
            _collectionView.DataSource = dataSource;
        }

        public void SetCollectionView(IReadOnlyList<EventQuestSelectElementViewModel> items)
        {
            _noContentText.SetActive(items.Count == 0);
            _collectionView.ReloadData();
        }

        public void PlayChildScaler()
        {
            _childScaler.Play();    // インゲームから戻った際を考慮し、ViewDidAppear()で再生
        }

        public void SetRemainingAtText(string limitAtText)
        {
            _remainingAtText.SetText(limitAtText);
        }
        public void SetCellTitleEventOpenText(bool isOpen)
        {
            _cellTitleEventOpenText.gameObject.SetActive(isOpen);
            _cellTitleEventClosedText.gameObject.SetActive(!isOpen);
        }

        public void SetBackground(EventQuestBackgroundComponent bgComponent)
        {
            Instantiate(bgComponent, _backgroundArea.transform);
        }

        public void SetEventCampaignBalloon(RemainingTimeSpan remainingTimeSpan)
        {
            _campaignBalloonGameObject.SetActive(remainingTimeSpan.HasValue());
            _eventCampaignBalloon.SetRemainingTimeText(remainingTimeSpan);
        }

        public void SetMissionBadgeActive(bool isActive)
        {
            _missionBadgeObj.SetActive(isActive);
        }

        public void SetEventExchangeShopActive(bool isActive)
        {
            _eventExchangeShopObj.SetActive(isActive);
        }

        public void SetAdventBattleButtonActive(
            AdventBattleOpenStatus status,
            AdventBattleName adventBattleName,
            AdventBattleRemainTimeSentence sentence)
        {
            //ボタン
            _adventBattleButton.gameObject.SetActive(status.ButtonVisible);
            _adventBattleButton.interactable = status.ButtonInteractable;
            _adventBattleGrayOutObj.SetActive(status.GrayOutVisible);

            //テキスト
            _adventBattleName.SetText(adventBattleName.Value);
            _adventBattleRemainingTimeObj.SetActive(status.RemainTimeVisible);
            _adventBattleRemainingTimePrefixText.SetText(sentence.PrefixString);
            _adventBattleRemainingTimeText.SetText(sentence.RemainingTimeString);
        }

    }
}
