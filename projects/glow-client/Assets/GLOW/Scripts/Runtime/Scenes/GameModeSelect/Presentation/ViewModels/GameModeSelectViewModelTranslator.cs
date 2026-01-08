using System.Linq;
using GLOW.Scenes.GameModeSelect.Domain;

namespace GLOW.Scenes.GameModeSelect.Presentation
{
    public static class GameModeSelectViewModelTranslator
    {
        public static GameModeSelectViewModel Translate(GameModeSelectUseCaseModel model)
        {
            var items = model.Items.Select(ToItemViewModel).ToList();
            return new GameModeSelectViewModel(items);
        }
        static GameModeSelectItemViewModel ToItemViewModel(GameModeSelectUseCaseItemModel model)
        {
            return new GameModeSelectItemViewModel(
                model.IsSelected,
                model.Type,
                model.MstEventId,
                model.MstEventEndAt,
                model.AssetKey,
                model.EventAssetKey,
                model.LimitTime);
        }
    }
}
