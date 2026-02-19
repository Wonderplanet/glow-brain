using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Presentation.Modules.Audio;
using UIKit;
using UnityEngine;
using UnityEngine.EventSystems;
using Wonderplanet.UIHaptics.Presentation;
using WPFramework.Presentation.Views;

namespace GLOW.Core.Presentation.CustomCarousel
{
    public class CellDragHandler
    {
        bool _dragging;
        public bool IsDragging => _dragging;

        readonly DraggingCellActionHandler _actionHandler = new DraggingCellActionHandler();
        public DraggingCellActionHandler ActionHandler => _actionHandler;

        public void Build(int initialIndex)
        {
            _actionHandler.InvokeCenterIndexActions(initialIndex);
        }

        public void UpdateDragging(bool isMove)
        {
            _dragging = isMove;
            _actionHandler.InvokeDraggingActions(isMove);
            if (!isMove)
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            }
        }

        public void UpdateIndex(int centerIndex)
        {
            _actionHandler.InvokeCenterIndexActions(centerIndex);
        }
    }

    public class DraggingCellActionHandler
    {
        readonly List<Action<bool>> _onChangeDraggingStatuses = new List<Action<bool>>();
        readonly List<Action<int>> _onChangeCenterIndexes = new List<Action<int>>();

        public void AddActionOnChangeDragging(Action<bool> onChangeDraggingStatus)
        {
            _onChangeDraggingStatuses.Add(onChangeDraggingStatus);
        }
        public void AddActionOnChangeCenterIndex(Action<int> onChangeCenterIndex)
        {
            _onChangeCenterIndexes.Add(onChangeCenterIndex);
        }
        public void ClearActions()
        {
            _onChangeDraggingStatuses?.Clear();
            _onChangeCenterIndexes?.Clear();
        }
        public void InvokeDraggingActions(bool isMove)
        {
            if (_onChangeDraggingStatuses == null ||_onChangeDraggingStatuses.Count <= 0) return;
            foreach (var onChangeStatus in _onChangeDraggingStatuses) onChangeStatus?.Invoke(isMove);
        }
        public void InvokeCenterIndexActions(int currentIndex)
        {
            if (_onChangeCenterIndexes == null ||_onChangeCenterIndexes.Count <= 0) return;
            foreach (var onChangeCenterIndex in _onChangeCenterIndexes) onChangeCenterIndex?.Invoke(currentIndex);
        }
    }
    [DisallowMultipleComponent]
    public class GlowCustomInfiniteCarouselView : UIComponent,
        IBeginDragHandler,
        IEndDragHandler,
        IDragHandler,
        IPointerDownHandler,
        IPointerUpHandler,
        IInfiniteCarouselCellDelegate,
        ICarouselButtonMovableHandler
    {
        [SerializeField] InfiniteCarouselCell _cellPrefab;

        [Space]

        [SerializeField] RectTransform _content;
        [SerializeField] int _spacing = 10;
        [SerializeField] int _bufferedCellCount = 4;

        [Space]

        [SerializeField] float _focusAreaSize = 250;
        [SerializeField] float _cellSize = 300;

        [Space]

        [SerializeField] [Tooltip("cellスクロール中におけるcell確定のための慣性(velocity)しきい値")] float _inertiaThreshold = 0.3f;
        [SerializeField] [Tooltip("スワイプ離しても動き続けるvelocityの倍率")] float _inertiaApplyRate = 1f;
        [SerializeField] float _centeringDumping = 4f;
        [SerializeField] [Tooltip("velocity減速していく量の補正値")]float _velocityDeceleration = 3f;
        [SerializeField] [Tooltip("OnTapとかで動かしたときの移動速度")] int _seekSpeed = 600;

        [Space]

        [SerializeField] bool _useUnscaledTime;

        [Space]

        [SerializeField] bool _looping;  // true:ループする、false:ループしない
        [SerializeField] bool _leftDirection; // true:左方向にスライド、false:右方向にスライド

        [Space]
        [Header("インジケーター")]
        [SerializeField] GlowCustomInfiniteCarouselIndicatorControl _indicatorControl;

        [SerializeField] bool _useIndicator;

        readonly List<InfiniteCarouselCell> _reusableCells = new List<InfiniteCarouselCell>();
        readonly Dictionary<int, GlowCustomInfiniteCarouselCell> _cellTable = new Dictionary<int, GlowCustomInfiniteCarouselCell>();
        readonly List<int> _activeCellIndexes = new List<int>();
        readonly List<int> _shouldEnqueueCells = new List<int>();
        readonly HashSet<int> _touchIndexes = new HashSet<int>();

        int _numberOfItems = 1;

        readonly CellDragHandler _cellDragHandler = new CellDragHandler();
        public CellDragHandler CellDragHandler => _cellDragHandler;

        Vector2 _dragBeginPosition;
        int _currentIndex;//参照が多いので代入のときにだけ利用する
        public int CurrentIndex => _currentIndex;
        float _scroll;
        float _velocity;
        int _pointerId;
        bool _seek;
        int _seekIndex;
        bool _isBuild;

        IGlowCustomCarouselViewDataSource _dataSource;

        public IHapticsPresenter HapticsPresenter { get; set; }
        void OnApplicationFocus(bool hasFocus)
        {
            if (hasFocus) {
                // Debug.Log($"アプリが選択された(バックグラウンドから戻った)");
                HapticsPresenter.SyncRestartEngine();
            }
            else {
                // Debug.Log($"アプリが選択されなくなった(バックグラウンドに行った)");
            }
        }

        public void PointerReset()
        {
            _cellDragHandler.UpdateIndex(CurrentIndex);

        }

        public bool IsMove()
        {
            return _cellDragHandler.IsDragging;
        }


        public IGlowCustomCarouselViewDataSource DataSource
        {
            get => _dataSource;
            set
            {
                _dataSource = value;
                Build();
            }
        }

        public IGlowCustomCarouselViewDelegate ViewDelegate { get; set; }

        public RectTransform Content => _content;

        public InfiniteCarouselCell SelectedCell => _cellTable.GetValueOrDefault(CurrentIndex);
        public RectTransform RectTransform => (RectTransform)transform;

        public T DequeueReusableCell<T>() where T : InfiniteCarouselCell
        {
            if (_reusableCells.Count <= 0)
            {
                return Instantiate(_cellPrefab) as T;
            }

            var cell = _reusableCells[0];
            _reusableCells.Remove(cell);
            return cell as T;
        }

        void EnqueueReusableCell(InfiniteCarouselCell cell)
        {
            _reusableCells.Add(cell);
        }

        public void ClearUnusedCell()
        {
            foreach (var i in _reusableCells.ToList())
            {
                Destroy(i.gameObject);
            }

            _reusableCells.Clear();
        }

        void Build()
        {
            DismissAllCells();
            _numberOfItems = DataSource.NumberOfItems();
            if (_numberOfItems == 0)
            {
                return;
            }

            _currentIndex = DataSource.SelectedIndex();
            // NOTE: _currentIndexを中心として左右にセルを配置する
            //       例えば_bufferedCellCountが2の場合は以下のようになる
            //       -2, -1, 0, 1, 2
            var cellCount = _bufferedCellCount * 2 + 1;
            for (var i = 0; i < cellCount; i++)
            {
                var index = CurrentIndex - _bufferedCellCount + i;
                PresentCell(index);
            }

            SetUpIndicator();
            PostBuild();

            if (!_isBuild)
            {
                _isBuild = true;
            }
        }

        void PostBuild()
        {
            _cellDragHandler.Build(CurrentIndex);
        }

        bool ExistIndicator()
        {
            return _indicatorControl != null;
        }
        void SetUpIndicator()
        {
            if (!ExistIndicator()) return;

            _indicatorControl.Hidden = !_useIndicator;

            if (DataSource == null)
            {
            }
            else
            {
                _indicatorControl.NumberOfPages = DataSource.NumberOfItems();
                _indicatorControl.CurrentPage = CurrentIndex;
                // _indicatorControl.OnClickEvent = OnPageControlEvent;
            }
        }
        void UpdateIndicatorPage(int index)
        {
            _indicatorControl.CurrentPage = index;
        }

        int GetActualIndex(int index)
        {
            return (index + (Mathf.Abs(index / _numberOfItems) + 1) * _numberOfItems) % _numberOfItems;
        }

        void PresentCell(int index)
        {
            if (_cellTable.ContainsKey(index))
            {
                return;
            }

            var cell = DataSource.CellForItemAtIndex(GetActualIndex(index));
            cell.Index = index;
            cell.Hidden = false;
            cell.transform.SetParent(_content, false);
            cell.RegisterDelegate(this);

            LayoutWithIndex(cell, index);
            cell.AccessoryButtonTapEvent.AddListener((identifier) => AccessoryButtonTap(index, identifier));
            _cellDragHandler.ActionHandler.AddActionOnChangeDragging(cell.OnChangeDraggingStatus);
            _cellDragHandler.ActionHandler.AddActionOnChangeCenterIndex(cell.OnChangeCenterIndex);
            _cellTable.Add(index, cell);
            _activeCellIndexes.Add(index);
        }

        void OnPositionUpdate(int index)
        {
            // NOTE: 選択対象が同じだった場合は何もしない
            if (CurrentIndex == index)
            {
                return;
            }

            var diffIndex = index - CurrentIndex;
            _scroll += diffIndex * GetDirectionIndex() * (_cellSize + _spacing);

            //この2行は順番依存
            _currentIndex = index;
            _cellDragHandler.UpdateIndex(CurrentIndex);

            if(ExistIndicator()) UpdateIndicatorPage(index);

            if (!_seek)
            {
                HapticsPresenter.Impact();
                ViewDelegate.DidSelectItemAtIndex(GetActualIndex(CurrentIndex));
            }

            _touchIndexes.Clear();

            foreach (var activeIndex in _activeCellIndexes)
            {
                var leftSideIndex = CurrentIndex - _bufferedCellCount;
                var rightSideIndex = CurrentIndex + _bufferedCellCount;
                if (leftSideIndex <= activeIndex && rightSideIndex >= activeIndex)
                {
                    _touchIndexes.Add(activeIndex);
                }
            }

            _shouldEnqueueCells.Clear();
            foreach (var activeIndex in _activeCellIndexes)
            {
                if (_touchIndexes.Contains(activeIndex))
                {
                    continue;
                }

                _shouldEnqueueCells.Add(activeIndex);
            }

            foreach (var cellIndex in _shouldEnqueueCells)
            {
                DismissCell(cellIndex);
            }

            for (var i = -_bufferedCellCount; i <= _bufferedCellCount; i++)
            {
                var presentIndex = CurrentIndex + i;
                if (!_touchIndexes.Contains(presentIndex))
                {
                    PresentCell(presentIndex);
                }
            }
        }

        void DismissCell(int index)
        {
            if (_cellTable.TryGetValue(index, out var cell) && cell)
            {
                cell.UnregisterDelegate(this);
                cell.AccessoryButtonTapEvent.RemoveAllListeners();
                EnqueueReusableCell(cell);
            }

            _cellTable.Remove(index);
            _activeCellIndexes.Remove(index);
        }

        void OnScroll(float movementAmount)
        {
            if (_seek)
            {
                return;
            }

            // NOTE: 移動量に制限をかける
            movementAmount = Mathf.Clamp(movementAmount, -50, 50);
            _scroll = AdjustScroll(_scroll + movementAmount);

            _velocity = movementAmount * _inertiaApplyRate;
        }

        void LateUpdate()
        {
            if (!_isBuild) return;

            if (_seek)
            {
                Seek();
            }
            else if (!_cellDragHandler.IsDragging)
            {
                Inertia();
            }

            LayoutCells();
            SelectionUpdate();
        }

        void Seek()
        {
            var diffIndex = _seekIndex - CurrentIndex;
            _scroll = AdjustScroll(_scroll - (diffIndex * GetDirectionIndex() * GetAnimationDeltaTime() * _seekSpeed));

            if (diffIndex == 0)
            {
                _seek = false;
            }
        }

        void Inertia()
        {
            //慣性しきい値以下か？
            var isInertiaThreshold = Mathf.Abs(_velocity) < _inertiaThreshold;

            if (isInertiaThreshold)
            {
                SelectedCell.transform.SetAsLastSibling();
                _scroll = AdjustScroll(Mathf.Lerp(_scroll, 0, GetAnimationDeltaTime() * _centeringDumping));
            }
            else
            {
                _scroll = AdjustScroll(_scroll + _velocity);
                _velocity = Mathf.Lerp(_velocity, 0, GetAnimationDeltaTime() * _velocityDeceleration);
            }
        }

        void SelectionUpdate()
        {
            // NOTE: セル間の実際の距離（_cellSize + _spacing）の半分を判定基準とすることで、
            //       OnPositionUpdate()での補正量と整合性を保つ
            //       これにより、_spacingがマイナス値でもタップ時に正しく中央に移動できる
            var actualCellDistance = _cellSize + _spacing;
            var divisor = actualCellDistance / 2;

            // divisorが小さすぎると敏感になりすぎるため、最小値を設定
            // 実際の距離の1/4を最小値とすることで、極端な値でも安定動作させる
            var minDivisor = Mathf.Max(actualCellDistance / 4, 10f);
            if (divisor < minDivisor)
            {
                divisor = minDivisor;
            }

            var diffIndex = Mathf.Clamp((int)(_scroll / divisor), -1, 1);
            var index = CurrentIndex - (diffIndex * GetDirectionIndex());
            OnPositionUpdate(index);
        }

        void Layout(InfiniteCarouselCell cell, float x, int index)
        {
            if (!_looping && (index < 0 || (_numberOfItems - 1) < index))
            {
                // ループなしで範囲外だったら非表示
                cell.Hidden = true;
            }
            else
            {
                var position = cell.RectTransform.localPosition;
                position.x = x;
                cell.RectTransform.localPosition = position;
                cell.Hidden = false;
            }
        }

        void LayoutWithIndex(InfiniteCarouselCell cell, int index)
        {
            // TODO: _focusAreaSizeと_cellSizeに差があると計算が狂い始めるため今の所は同じ値を入れることを推奨する
            var diffIndex = index - CurrentIndex;
            // NOTE: 0の場合は中央に配置しそれ以外は左右に配置する
            if (diffIndex == 0)
            {
                Layout(cell, Mathf.Sign(_scroll) * (_focusAreaSize / 2 - _cellSize / 2) + _scroll, index);
            }
            else
            {
                var x = (Mathf.Abs(diffIndex) - 1) * (_cellSize + _spacing) + _cellSize / 2 + _focusAreaSize / 2 + _spacing;
                Layout(cell, Mathf.Sign(diffIndex) * GetDirectionIndex() * x + _scroll, index);
            }

            ViewDelegate?.DidLayoutCell(cell, GetActualIndex(index));
        }

        void LayoutCells()
        {
            foreach (var index in _activeCellIndexes)
            {
                var cell = _cellTable[index];
                LayoutWithIndex(cell, index);
            }
        }

        void IBeginDragHandler.OnBeginDrag(PointerEventData eventData)
        {
            _pointerId = eventData.pointerId;
            _cellDragHandler.UpdateDragging(true);
            _dragBeginPosition = GetLocalPosition(eventData.position, eventData.enterEventCamera);
        }

        void IDragHandler.OnDrag(PointerEventData eventData)
        {
            if (_pointerId != eventData.pointerId)
            {
                return;
            }

            var position = GetLocalPosition(eventData.position, eventData.enterEventCamera);
            var delta = position.x - _dragBeginPosition.x;
            _dragBeginPosition = position;
            OnScroll(delta);
        }

        void IEndDragHandler.OnEndDrag(PointerEventData eventData)
        {
            _pointerId = 0;
            _cellDragHandler.UpdateDragging(false);
        }

        void IPointerDownHandler.OnPointerDown(PointerEventData eventData)
        {
            _cellDragHandler.UpdateDragging(true);
            _dragBeginPosition = GetLocalPosition(eventData.position, eventData.enterEventCamera);
            OnScroll(0);

        }

        void IPointerUpHandler.OnPointerUp(PointerEventData eventData)
        {
            _cellDragHandler.UpdateDragging(false);
        }

        Vector2 GetLocalPosition(Vector2 screenPosition, Camera uiCamera)
        {
            if (!uiCamera)
            {
                return screenPosition;
            }

            RectTransformUtility.ScreenPointToLocalPointInRectangle(
                (RectTransform)transform,
                screenPosition,
                uiCamera,
                out var result);
            return result;
        }

        void IInfiniteCarouselCellDelegate.OnTap(int index)
        {
            _cellDragHandler.UpdateDragging(false);

            if(CurrentIndex == index)
                return;

            MoveFromTap(index);
        }
        void IInfiniteCarouselCellDelegate.OnPointerDown(int index)
        {
            // NOTE: 静止時は何もしない
            if(Mathf.Abs(_velocity) < 0.3f)
            {
                return;
            }
            // NOTE: セルのタップ時(押した時)に静止する
            _cellDragHandler.UpdateDragging(false);
            OnScroll(0);
        }

        public void OnPointerDown(int index)
        {
        }

        public void MoveLeft()
        {
            if (_looping)
            {
                var index = CurrentIndex - 1;
                MoveFromTap(index);
            }
            else
            {
                var index = CurrentIndex - 1;
                //左端だったら何もしない
                if (index < 0)
                    return;

                MoveFromTap(index);
            }
        }
        public void MoveRight()
        {
            if (_looping)
            {
                var index = CurrentIndex + 1;
                MoveFromTap(index);
            }
            else
            {
                var index = CurrentIndex + 1;
                //右端だったら何もしない
                //indexを見るのに対して、NumberOfItemsはCountであることに注意
                if (DataSource.NumberOfItems()-1 < index)
                    return;
                MoveFromTap(index);
            }
        }

        void MoveFromTap(int index)
        {
            ViewDelegate.DidSelectItemAtIndex(GetActualIndex(index));
            _seek = true;
            _seekIndex = index;
            _velocity = 0;
            HapticsPresenter?.Impact();
        }

        public void ReloadData()
        {
            DismissAllCells();
            Build();
        }

        void DismissAllCells()
        {
            var cellIndexes = new List<int>(_activeCellIndexes);
            foreach (var index in cellIndexes)
            {
                DismissCell(index);
            }
        }

        float GetAnimationDeltaTime()
        {
            return _useUnscaledTime ? Time.unscaledDeltaTime : Time.deltaTime;
        }

        int GetDirectionIndex()
        {
            return _leftDirection ? -1 : 1;
        }

        float AdjustScroll(float scroll)
        {
            // ループする場合は補正しない
            if (_looping)
            {
                return scroll;
            }

            // ループしない場合は末端以上行かないようにscroll値を補正
            if (_leftDirection)
            {
                //　左方向の場合
                if (CurrentIndex <= 0 && scroll < 0.0f)
                {
                    return 0.0f;
                }
                if (CurrentIndex >= (_numberOfItems - 1) && scroll > 0.0f)
                {
                    return 0.0f;
                }
            }
            else
            {
                // 右方向の場合
                if (CurrentIndex <= 0 && scroll > 0.0f)
                {
                    return 0.0f;
                }
                if (CurrentIndex >= (_numberOfItems - 1) && scroll < 0.0f)
                {
                    return 0.0f;
                }
            }

            // 補正の必要なし
            return scroll;
        }

        void AccessoryButtonTap(int indexPath, object identifier)
        {
            if (ViewDelegate != null)
            {
                ViewDelegate.AccessoryButtonTappedForRowWith(this, indexPath, identifier);
            }
        }

        protected override void OnDestroy()
        {
            _cellDragHandler?.ActionHandler.ClearActions();
        }


    }
}
