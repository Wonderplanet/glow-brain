using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Localization;
using WPFramework.Modules.Localization;
using Zenject;

namespace GLOW.Core.Data.Repositories
{
    public class BaseMstRepository
    {
        [Inject] ILocalizationInformationProvider LocalizationInformationProvider { get; }

        [Inject] IMstModelCache ModelCache { get; }

        protected BaseMstRepository()
        {
            // NOTE: 単独でインスタンス化することを禁止するため、protectedコンストラクタを定義
        }

        protected Language CurrentLanguage => LanguageConverter.ToLanguage(LocalizationInformationProvider.LocaleCode);

        protected void ClearModelCache()
        {
            ModelCache.Clear();
        }

        protected void RemoveModelCache<T>()
        {
            ModelCache.Remove<T>();
        }

        protected void RemoveModelCache<T>(string key)
        {
            ModelCache.Remove<T>(key);
        }

        protected IEnumerable<T> GetOrCreateModelCache<T>(Func<IEnumerable<T>> createInstance)
        {
            return ModelCache.GetOrCreate(createInstance);
        }

        protected IEnumerable<T> GetOrCreateModelCache<T>(string key, Func<IEnumerable<T>> createInstance)
        {
            return ModelCache.GetOrCreate(key, createInstance);
        }
    }
}
