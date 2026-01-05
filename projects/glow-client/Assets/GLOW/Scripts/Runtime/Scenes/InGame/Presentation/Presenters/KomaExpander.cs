using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.Views;

namespace GLOW.Scenes.InGame.Presentation.Presenters
{
    /// <summary>
    /// キャラが必殺技を使用する際のコマのExpanding表示を管理するクラス
    /// </summary>
    public class KomaExpander
    {
        readonly InGameViewController _viewController;
        readonly IViewCoordinateConverter _viewCoordinateConverter;
        readonly MstPageModel _mstPageModel;
        readonly Dictionary<FieldObjectId, MultipleSwitchHandler> _specialAttackKomaExpandingHandlerDictionary = new();

        public KomaExpander(
            InGameViewController viewController,
            IViewCoordinateConverter viewCoordinateConverter,
            MstPageModel mstPageModel)
        {
            _viewController = viewController;
            _viewCoordinateConverter = viewCoordinateConverter;
            _mstPageModel = mstPageModel;
        }

        /// <summary>
        /// 必殺技使用時のコマExpanding処理
        /// </summary>
        public void ExpandKomaIfNeeded(IReadOnlyList<CharacterUnitModel> unitModels)
        {
            foreach (var unit in unitModels)
            {
                // 必殺ワザ開始時に、そのキャラがいるコマ行のキャラ表示をコマ枠より前面にする
                if (unit.IsStateStart(UnitActionState.SpecialAttack))
                {
                    // 高いユニットがいる場合はExpandingしない
                    if (!ShouldExpandKomaSetFieldImage(unit, unitModels)) continue;
                    
                    var pos = _viewController.GetUnitViewPos(unit.Id);
                    var handler = _viewController.ExpandKomaSetFieldImage(pos);

                    if (_specialAttackKomaExpandingHandlerDictionary.ContainsKey(unit.Id))
                    {
                        _specialAttackKomaExpandingHandlerDictionary[unit.Id]?.Dispose();
                    }

                    _specialAttackKomaExpandingHandlerDictionary[unit.Id] = handler;
                    continue;
                }

                // 必殺ワザが終わったら、キャラの前面表示を戻す
                if (_specialAttackKomaExpandingHandlerDictionary.ContainsKey(unit.Id)
                    && _specialAttackKomaExpandingHandlerDictionary[unit.Id] != null
                    && unit.Action.ActionState != UnitActionState.SpecialAttack)
                {
                    _specialAttackKomaExpandingHandlerDictionary[unit.Id].Dispose();
                    _specialAttackKomaExpandingHandlerDictionary.Remove(unit.Id);
                }
            }
        }

        /// <summary>
        /// コマExpandingをリセット
        /// </summary>
        public void ResetKomaExpanding()
        {
            foreach (var handler in _specialAttackKomaExpandingHandlerDictionary.Values)
            {
                handler?.Dispose();
            }

            _specialAttackKomaExpandingHandlerDictionary.Clear();
        }

        /// <summary>
        /// コマのExpanding表示を行うべきかチェック
        /// </summary>
        bool ShouldExpandKomaSetFieldImage(CharacterUnitModel targetUnit, IReadOnlyList<CharacterUnitModel> allUnits)
        {
            var targetKomaLine = _mstPageModel.GetKomaLine(targetUnit.LocatedKoma.Id);
            if (targetKomaLine.KomaList.Count == 0) return false;
            
            // コマの高さを取得
            var fieldVec = new FieldCoordV2(0, targetKomaLine.Height);
            var fieldViewVec = _viewCoordinateConverter.ToFieldViewCoord(fieldVec);
            var komaHeight = fieldViewVec.Y;
            
            // チェック対象のコマを収集
            var komasToCheck = new HashSet<KomaId>(6);
            
            // targetUnitがいるコマLineに属するすべてのコマのKomaIdを追加
            foreach (var koma in targetKomaLine.KomaList)
            {
                komasToCheck.Add(koma.KomaId);
            }
            
            // コマLineの前後1コマもチェック対象
            var prevKomaNo = _mstPageModel.GetKomaNo(targetKomaLine.KomaList[0].KomaId) - 1;
            komasToCheck.Add(_mstPageModel.GetKoma(prevKomaNo).KomaId);
            
            var nextKomaNo = _mstPageModel.GetKomaNo(targetKomaLine.KomaList.Last().KomaId) + 1;
            komasToCheck.Add(_mstPageModel.GetKoma(nextKomaNo).KomaId);
            
            // 指定されたコマにいるユニットの高さをチェック
            foreach (var unit in allUnits)
            {
                if (unit.Id == targetUnit.Id) continue;
                if (unit.LocatedKoma.IsEmpty()) continue;
                
                // ユニットが対象のコマまたはその前後のコマにいるかチェック
                if (komasToCheck.Contains(unit.LocatedKoma.Id))
                {
                    // ユニットの高さを取得
                    var unitView = _viewController.GetUnitView(unit.Id);
                    if (unitView != null && unitView.UnitImage.UnitHeight > komaHeight)
                    {
                        // 高いユニットが存在する場合はExpandingしない
                        return false;
                    }
                }
            }
            
            return true;
        }
    }
}