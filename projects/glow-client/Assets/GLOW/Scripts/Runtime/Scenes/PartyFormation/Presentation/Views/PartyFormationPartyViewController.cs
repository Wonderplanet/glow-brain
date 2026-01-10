using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.PartyFormation.Presentation.Presenters;
using GLOW.Scenes.PartyFormation.Presentation.ViewModels;
using GLOW.Scenes.UnitList.Domain.Constants;
using UIKit;
using UnityEngine;
using UnityEngine.EventSystems;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public class PartyFormationPartyViewController : UIViewController<PartyFormationPartyView>, IPartyFormationPartyViewController
    {
        public record Argument(
            PartyNo PartyNo,
            IPartyFormationUnitLongPressDelegate LongPressDelegate,
            MasterDataId SpecialRuleTargetMstId,
            InGameContentType SpecialRuleContentType,
            EventBonusGroupId EventBonusGroupId,
            UnitSortFilterCacheType UnitSortFilterCacheType);

        [Inject] IPartyFormationPartyViewDelegate ViewDelegate { get; }
        [Inject] IUnitImageLoader UnitImageLoader { get; }
        [Inject] IUnitImageContainer UnitImageContainer { get; }
        [Inject] Argument Args { get; }

        PartyMemberIndex _draggingIndex = PartyMemberIndex.Empty;
        PartyMemberIndex _previewedIndex = PartyMemberIndex.Empty;

        public PartyNo PartyNo { get; private set; }
        public PartyName PartyName { get; private set; }
        public TotalPartyStatus TotalPartyStatus { get; private set; }
        public TotalPartyStatusUpperArrowFlag TotalPartyStatusUpperArrowFlag { get; private set; }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.ViewWillAppear();
            ActualView.RegisterLongPress(Args.LongPressDelegate);
        }

        public void UpdateView()
        {
            ViewDelegate.UpdateView();
        }

        public void Setup(PartyFormationPartyViewModel viewModel)
        {
            ActualView.Setup(viewModel, UnitImageLoader, UnitImageContainer);
            PartyNo = viewModel.PartyNo;
            PartyName = viewModel.Name;
            TotalPartyStatus = viewModel.TotalPartyStatus;
            TotalPartyStatusUpperArrowFlag = viewModel.TotalPartyStatusUpperArrowFlag;
        }

        /// <summary>
        /// ドラッグ中のパーティリスト処理
        /// タップ位置にドラッグ中のキャラを割り込ませる
        /// </summary>
        public void OnDragEvent(UserDataId selectedUnitId, PointerEventData eventData)
        {
            var index = CalcCollisionIndex(eventData);
            if(_draggingIndex == index) return;

            var selectedUnitIndex = ActualView.GetMemberIndex(selectedUnitId);
            _draggingIndex = index;


            if (index.IsEmpty())
            {
                // 枠外にドラッグした場合
                ActualView.SetDefaultModeAll();
                ActualView.SetPreviewModeForTargetFrameOut(selectedUnitIndex);
            }

            else if (_previewedIndex.IsEmpty())
            {
                // 枠外から枠内にドラッグした場合
                ActualView.SetDefaultModeAll();
                ActualView.SetPreviewMode(selectedUnitIndex, index);
            }
            else
            {
                // 枠内でドラッグした場合
                if (_previewedIndex.Value < index.Value)
                {
                    ActualView.SetDefaultMode(_previewedIndex, index);
                }
                else
                {
                    ActualView.SetDefaultMode(index, _previewedIndex);
                }
                ActualView.SetPreviewMode(selectedUnitIndex, index);
            }

            _previewedIndex = index;
        }

        /// <summary>
        /// D＆D終了時のタップ位置とアバターの衝突判定を行う
        /// </summary>
        /// <param name="eventData"></param>
        /// <param name="onDropAction"></param>
        public void OnDropAvatar(PointerEventData eventData, Action<PartyMemberIndex> onDropAction)
        {
            var index = CalcCollisionIndex(eventData);
            onDropAction?.Invoke(index);
            _draggingIndex = PartyMemberIndex.Empty;
            _previewedIndex = PartyMemberIndex.Empty;
        }

        public void SetPreviewMode(UserDataId userUnitId, bool isHidden)
        {
            var index = ActualView.GetMemberIndex(userUnitId);
            if (index.IsEmpty()) return;
            _draggingIndex = index;
            _previewedIndex = index;

            if (isHidden)
            {
                ActualView.SetPreviewModeForEmpty(index);
            }
            else
            {
                ActualView.SetDefaultMode(index);
            }
        }

        public void SetScrollMode(bool isScroll)
        {
            ActualView.SetScrollMode(isScroll);
        }

        PartyMemberIndex CalcCollisionIndex(PointerEventData eventData)
        {
            RectTransformUtility.ScreenPointToLocalPointInRectangle(ActualView.RectTransform, eventData.position,
                eventData.enterEventCamera, out var localPosition);
            return ActualView.GetCollisionIndex(localPosition);
        }
    }
}
