using System;
using System.Globalization;
using WonderPlanet.StorageSupporter.Utils;

namespace GLOW.Scenes.Title.Domains.Formatter
{
    public static class DataSizeFormatter
    {
        public static string ConvertToSizeMBOrMore(ulong sizeInBytes)
        {
            // NOTE: 少数2桁までのMB表示にする
            var size = Math.Round(ConvertSizeMBOrMore(sizeInBytes), 2, MidpointRounding.AwayFromZero);

            // NOTE: 0.01MB未満の場合は0.01MBとして返す
            //       InvariantCultureを指定して、カンマ区切りにしない
            return size.ToString(CultureInfo.InvariantCulture);
        }

        public static string ConvertToStringMBOrMore(ulong sizeInBytes)
        {
            // NOTE: 少数2桁まで表示
            var size = Math.Round(ConvertSizeMBOrMore(sizeInBytes), 2, MidpointRounding.AwayFromZero);
            var sizeUnits = ConvertToTargetUnitMBorMore(sizeInBytes);
            var sizeUnitName = DataSizeUnitName.GetSizeUnitName(sizeUnits);

            return  $"{size.ToString("N2", CultureInfo.InvariantCulture)} {sizeUnitName}";
        }

        static decimal ConvertSizeMBOrMore(ulong sizeInBytes)
        {
            if (sizeInBytes == 0)
            {
                return 0;
            }

            // NOTE: MB以上の単位に変換
            var size = (decimal)sizeInBytes / 1024 / 1024;

            // NOTE: GB以上の場合
            while (size >= 1024)
            {
                size /= 1024;
            }

            // NOTE: 0.01MB未満の場合は0.01MBとして返す
            return size == 0 ? (decimal)0.01 : size;
        }

        public static string ConvertToTargetUnitSize(ulong sizeInBytes, DataSizeUnits decimalPlaces)
        {
            if (sizeInBytes == 0)
            {
                return "0";
            }

            decimal size = sizeInBytes;
            var sizeUnits = 0;

            // NOTE: 指定した単位まで変換
            while (sizeUnits < (int)decimalPlaces)
            {
                size /= 1024;
                sizeUnits++;
            }

            // NOTE: 0.01MB未満の場合は0.01MBとして返す
            return size == 0 ? "0.01" : Math.Round(size, 2, MidpointRounding.AwayFromZero).ToString();
        }

        public static DataSizeUnits ConvertToTargetUnitMBorMore(ulong sizeInBytes)
        {
            // NOTE: MB以上の単位に変換
            var size = (decimal)sizeInBytes / 1024 / 1024;
            var sizeUnits = 2;

            while (size >= 1024)
            {
                size /= 1024;
                sizeUnits++;
            }

            return (DataSizeUnits)sizeUnits;
        }
    }
}
