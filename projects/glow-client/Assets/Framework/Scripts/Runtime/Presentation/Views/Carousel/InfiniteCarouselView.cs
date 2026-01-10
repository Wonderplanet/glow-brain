using System.Collections.Generic;
using System.Linq;
using UIKit;
using UnityEngine;
using UnityEngine.EventSystems;

namespace WPFramework.Presentation.Views
{
    [DisallowMultipleComponent]
    public class InfiniteCarouselView : UIComponent,
        IBeginDragHandler,
        IEndDragHandler,
        IDragHandler,
        IPointerDownHandler,
        IPointerUpHandler,
        IInfiniteCarouselCellDelegate
    {
        [SerializeField] InfiniteCarouselCell _cellPrefab = null;

        [Space]

        [SerializeField] RectTransform _content = null;
        [SerializeField] int _spacing = 10;
        [SerializeField] int _bufferedCellCount = 4;

        [Space]

        [SerializeField] float _focusAreaSize = 250;
        [SerializeField] float _cellSize = 300;

        [Space]

        [SerializeField] float _inertiaThreshold = 0.3f;
        [SerializeField] float _inertiaApplyRate = 1f;
        [SerializeField] float _centeringDumping = 4f;
        [SerializeField] float _velocityDeceleration = 3f;
        [SerializeField] int _seekSpeed = 600;

        [Space]

        [SerializeField] bool _useUnscaledTime = false;

        [Space]

        [SerializeField] bool _looping = false;  // true:ループする、false:ループしない
        [SerializeField] bool _leftDirection = false; // true:左方向にスライド、false:右方向にスライド
        [SerializeField] bool _onTouchStop = false; // true:タッチ時に静止する、false:タッチ時に静止しない

        readonly List<InfiniteCarouselCell> _reusableCells = new List<InfiniteCarouselCell>();
        readonly Dictionary<int, InfiniteCarouselCell> _cellTable = new Dictionary<int, InfiniteCarouselCell>();
        readonly List<int> _activeCellIndexes = new List<int>();
        readonly List<int> _shouldEnqueueCells = new List<int>();
        readonly HashSet<int> _touchIndexes = new HashSet<int>();

        const float DefaultInertiaThreshold = 0.3f;

        int _numberOfItems = 1;
        bool _dragging = false;
        Vector2 _dragBeginPosition;
        int _currentIndex = 0;
        float _scroll = 0f;
        float _velocity = 0f;
        int _pointerId = 0;
        bool _seek = false;
        int _seekIndex = 0;

        IInfiniteCarouselViewDataSource _dataSource = null;

        public void PointerReset()
        {
            _dragging = false;
        }

        public bool IsMove()
        {
            return _dragging;
        }

        public IInfiniteCarouselViewDataSource DataSource
        {
            get => _dataSource;
            set
            {
                _dataSource = value;
                Build();
            }
        }

        public IInfiniteCarouselViewDelegate ViewDelegate { get; set; }

        public RectTransform Content => _content;

        InfiniteCarouselCell SelectedCell => _cellTable.GetValueOrDefault(_currentIndex);
        public RectTransform RectTransform => (RectTransform)transform;

        public int CurrentIndex => GetActualIndex(_currentIndex);

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
                var index = _currentIndex - _bufferedCellCount + i;
                PresentCell(index);
            }
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

            _cellTable.Add(index, cell);
            _activeCellIndexes.Add(index);
        }

        void OnPositionUpdate(int index)
        {
            // NOTE: 選択対象が同じだった場合は何もしない
            if (_currentIndex == index)
            {
                return;
            }

            var diffIndex = index - _currentIndex;
            _scroll += diffIndex * GetDirectionIndex() * (_cellSize + _spacing);

            _currentIndex = index;

            if (!_seek)
            {
                ViewDelegate.DidSelectItemAtIndex(GetActualIndex(_currentIndex));
            }

            _touchIndexes.Clear();

            foreach (var activeIndex in _activeCellIndexes)
            {
                var leftSideIndex = _currentIndex - _bufferedCellCount;
                var rightSideIndex = _currentIndex + _bufferedCellCount;
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
                var presentIndex = _currentIndex + i;
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
            if (_seek)
            {
                Seek();
            }
            else if (!_dragging)
            {
                Inertia();
            }

            LayoutCells();
            SelectionUpdate();
        }

        void Seek()
        {
            var diffIndex = _seekIndex - _currentIndex;
            _scroll = AdjustScroll(_scroll - (diffIndex * GetDirectionIndex() * GetAnimationDeltaTime() * _seekSpeed));

            if (diffIndex == 0)
            {
                _seek = false;
            }
        }

        void Inertia()
        {
            if (Mathf.Abs(_velocity) < _inertiaThreshold)
            {
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
            var diffIndex = Mathf.Clamp((int)(_scroll / (_cellSize / 2 + _spacing)), -1, 1);
            var index = _currentIndex - (diffIndex * GetDirectionIndex());
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
            var diffIndex = index - _currentIndex;
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
            _dragging = true;
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
            _dragging = false;
        }

        void IPointerDownHandler.OnPointerDown(PointerEventData eventData)
        {
            _dragging = true;
            _velocity = 0;
        }

        void IPointerUpHandler.OnPointerUp(PointerEventData eventData)
        {
            _dragging = false;
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
            if (_onTouchStop)
            {
                _dragging = false;
            }

            // NOTE: セルのタップ時(離した時)にセルを中心に移動
            ViewDelegate.DidSelectItemAtIndex(GetActualIndex(index));
            _seek = true;
            _seekIndex = index;
            _velocity = 0;
        }

        void IInfiniteCarouselCellDelegate.OnPointerDown(int index)
        {
            if (!_onTouchStop)
            {
                return;
            }

            // NOTE: 静止時は何もしない
            if (Mathf.Abs(_velocity) < DefaultInertiaThreshold)
            {
                return;
            }
            // NOTE: セルのタップ時(押した時)に静止する
            _dragging = true;
            _velocity = 0;
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
                if (_currentIndex <= 0 && scroll < 0.0f)
                {
                    return 0.0f;
                }
                if (_currentIndex >= (_numberOfItems - 1) && scroll > 0.0f)
                {
                    return 0.0f;
                }
            }
            else
            {
                // 右方向の場合
                if (_currentIndex <= 0 && scroll > 0.0f)
                {
                    return 0.0f;
                }
                if (_currentIndex >= (_numberOfItems - 1) && scroll < 0.0f)
                {
                    return 0.0f;
                }
            }

            // 補正の必要なし
            return scroll;
        }
    }
}
