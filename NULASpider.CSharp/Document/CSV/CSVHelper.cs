using System;
using System.Dynamic;
using System.IO;
using CsvHelper;
using System.Data;
using System.Collections.Generic;
using Pchp.Core;

namespace nulastudio.Document.CSV
{
    public class CSVHelper
    {
        private string file;
        private StreamWriter writer;
        private CsvWriter csvWriter;
        private bool _hasData;

        public bool hasData { get => _hasData; }

        public CSVHelper(string file)
        {
            if (!File.Exists(file))
            {
                File.WriteAllText(file, "");
            }
            this.file = file;
            using (var reader = new StreamReader(file))
            using (var csv = new CsvReader(reader))
            {
                csv.Configuration.HasHeaderRecord = false;
                IEnumerable<dynamic> records = csv.GetRecords<dynamic>();
                foreach (IDictionary<string, object> record in records)
                {
                    _hasData = true;
                    break;
                }
            }
        }
        ~CSVHelper()
        {
            csvWriter.Flush();
            writer.Flush();
            csvWriter.Dispose();
            writer.Close();
        }
        public void writeRow(PhpArray data)
        {
            if (writer == null)
            {
                writer = new StreamWriter(this.file, true);
                csvWriter = new CsvWriter(writer);
            }
            csvWriter.Configuration.HasHeaderRecord = false;
            var row = new ExpandoObject() as IDictionary<string, object>;
            foreach (KeyValuePair<IntStringKey, PhpValue> item in data)
            {
                row[item.Key.ToString()] = item.Value.ToClr();
            }

            csvWriter.WriteRecord(row);
            csvWriter.Flush();
            writer.WriteLine();
            writer.Flush();
            _hasData = true;
        }
    }
}