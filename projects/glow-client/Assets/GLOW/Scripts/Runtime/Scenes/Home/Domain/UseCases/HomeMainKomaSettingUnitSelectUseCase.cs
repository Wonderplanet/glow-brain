using System.Collections.Generic;
using System.Linq;
using System.Runtime.InteropServices;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.Models;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class HomeMainKomaSettingUnitSelectUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IHomeMainKomaSettingFilterCacheRepository HomeMainKomaSettingFilterCacheRepository { get; }

        public HomeMainKomaSettingUnitSelectUseCaseModel GetModel(
            MasterDataId currentSettingMstUnitId,
            IReadOnlyList<MasterDataId> otherSettingMstUnitIds)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userUnits = gameFetchOther.UserUnitModels;
            var mstUnits = userUnits
                .Select(unit => MstCharacterDataRepository.GetCharacter(unit.MstUnitId))
                .ToList();

            var units = mstUnits
                .Where(m => IsSeriesUnit(m.MstSeriesId))
                .Select(mstUnit =>
                {
                    return new HomeMainKomaSettingUnitSelectItemUseCaseModel(
                        mstUnit.Id,
                        CharacterIconAssetPath.FromAssetKey(mstUnit.AssetKey),
                        CheckStatus(mstUnit.Id, currentSettingMstUnitId, otherSettingMstUnitIds));
                })
                .ToList();

            return new HomeMainKomaSettingUnitSelectUseCaseModel(units);
        }

        bool IsSeriesUnit(MasterDataId mstSeriesId)
        {
            var filterCache = HomeMainKomaSettingFilterCacheRepository.CachedFilterMstSeriesIds;
            if (!filterCache.Any())
            {
                return true;
            }
            return filterCache.Contains(mstSeriesId);
        }


        HomeMainKomaSettingUnitStatus CheckStatus(
            MasterDataId targetMstUnitId,
            MasterDataId currentSettingMstUnitId,
            IReadOnlyList<MasterDataId> otherSettingMstUnitIds)
        {
            // ターゲットが選択したユニットだったら選択中ステータス
            if (targetMstUnitId == currentSettingMstUnitId)
            {
                return HomeMainKomaSettingUnitStatus.Selected;
            }
            // 他のコマに設定されているユニットだったら他コマ選択済みステータス
            if (otherSettingMstUnitIds.Contains(targetMstUnitId))
            {
                return HomeMainKomaSettingUnitStatus.OtherSelected;
            }
            // それ以外は未選択状態ステータス
            else
            {
                return HomeMainKomaSettingUnitStatus.Unselected;
            }
        }
    }
}
