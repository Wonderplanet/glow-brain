using System.IO;
using UnityEngine;
using WPFramework.Constants.MasterData;

namespace GLOW.Core.Domain.Constants.MasterData
{
    public static class MstDataPath
    {
        const string MstDataDirectoryName = "mst_data";
        const string OprDataDirectoryName = "opr_data";
        // ReSharper disable once InconsistentNaming
        const string MstI18nDataDirectoryName = "mst_i18n_data";
        // ReSharper disable once InconsistentNaming
        const string OprI18nDataDirectoryName = "opr_i18n_data";

        public static string ParseFileNameFromPath(string path)
        {
            return Path.GetFileName(path);
        }

        public static string GetLocalDirectoryPath(MasterType masterType)
        {
            var directoryName = masterType switch
            {
                MasterType.Mst     => MstDataDirectoryName,
                MasterType.Opr     => OprDataDirectoryName,
                MasterType.MstI18n => MstI18nDataDirectoryName,
                MasterType.OprI18n => OprI18nDataDirectoryName,
                _ => string.Empty
            };
            return Path.Combine(Application.temporaryCachePath, directoryName);
        }

        public static string GetLocalFilePath(string fileName, MasterType masterType)
        {
            return Path.Combine(GetLocalDirectoryPath(masterType), fileName);
        }
    }
}
